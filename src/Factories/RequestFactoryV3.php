<?php

namespace AndrewSvirin\Ebics\Factories;

use AndrewSvirin\Ebics\Builders\Request\BodyBuilder;
use AndrewSvirin\Ebics\Builders\Request\DataEncryptionInfoBuilder;
use AndrewSvirin\Ebics\Builders\Request\DataTransferBuilder;
use AndrewSvirin\Ebics\Builders\Request\HeaderBuilder;
use AndrewSvirin\Ebics\Builders\Request\MutableBuilder;
use AndrewSvirin\Ebics\Builders\Request\OrderDetailsBuilder;
use AndrewSvirin\Ebics\Builders\Request\RequestBuilder;
use AndrewSvirin\Ebics\Builders\Request\StaticBuilder;
use AndrewSvirin\Ebics\Builders\Request\XmlBuilder;
use AndrewSvirin\Ebics\Builders\Request\XmlBuilderV3;
use AndrewSvirin\Ebics\Contexts\BTDContext;
use AndrewSvirin\Ebics\Contexts\BTFContext;
use AndrewSvirin\Ebics\Contexts\BTUContext;
use AndrewSvirin\Ebics\Contexts\RequestContext;
use AndrewSvirin\Ebics\Exceptions\EbicsException;
use AndrewSvirin\Ebics\Handlers\AuthSignatureHandlerV3;
use AndrewSvirin\Ebics\Handlers\OrderDataHandlerV3;
use AndrewSvirin\Ebics\Handlers\UserSignatureHandlerV3;
use AndrewSvirin\Ebics\Models\Bank;
use AndrewSvirin\Ebics\Models\Http\Request;
use AndrewSvirin\Ebics\Models\KeyRing;
use AndrewSvirin\Ebics\Models\UploadTransaction;
use AndrewSvirin\Ebics\Models\User;
use AndrewSvirin\Ebics\Models\UserSignature;
use AndrewSvirin\Ebics\Services\DigestResolverV3;
use DateTimeInterface;
use LogicException;

/**
 * Ebics 3.0 RequestFactory.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
final class RequestFactoryV3 extends RequestFactory
{
    /**
     * Constructor.
     *
     * @param Bank $bank
     * @param User $user
     * @param KeyRing $keyRing
     */
    public function __construct(Bank $bank, User $user, KeyRing $keyRing)
    {
        $this->authSignatureHandler = new AuthSignatureHandlerV3($keyRing);
        $this->userSignatureHandler = new UserSignatureHandlerV3($user, $keyRing);
        $this->orderDataHandler = new OrderDataHandlerV3($bank, $user, $keyRing);
        $this->digestResolver = new DigestResolverV3();
        parent::__construct($bank, $user, $keyRing);
    }

    protected function createRequestBuilderInstance(): RequestBuilder
    {
        return $this->requestBuilder
            ->createInstance(function (Request $request) {
                return new XmlBuilderV3($request);
            });
    }

    protected function addOrderType(OrderDetailsBuilder $orderDetailsBuilder, string $orderType): OrderDetailsBuilder
    {
        return $orderDetailsBuilder->addAdminOrderType($orderType);
    }

    /**
     * @throws EbicsException
     */
    public function createBTD(
        DateTimeInterface $dateTime,
        BTFContext $btfContext,
        DateTimeInterface $startDateTime = null,
        DateTimeInterface $endDateTime = null,
        int $segmentNumber = null,
        bool $isLastSegment = null
    ): Request {
        $context = (new RequestContext())
            ->setBank($this->bank)
            ->setUser($this->user)
            ->setKeyRing($this->keyRing)
            ->setDateTime($dateTime)
            ->setBTFContext($btfContext)
            ->setStartDateTime($startDateTime)
            ->setEndDateTime($endDateTime)
            ->setSegmentNumber($segmentNumber)
            ->setIsLastSegment($isLastSegment);

        $request = $this
            ->createRequestBuilderInstance()
            ->addContainerSecured(function (XmlBuilder $builder) use ($context) {
                $builder->addHeader(function (HeaderBuilder $builder) use ($context) {
                    $builder->addStatic(function (StaticBuilder $builder) use ($context) {
                        $builder
                            ->addHostId($context->getBank()->getHostId())
                            ->addRandomNonce()
                            ->addTimestamp($context->getDateTime())
                            ->addPartnerId($context->getUser()->getPartnerId())
                            ->addUserId($context->getUser()->getUserId())
                            ->addProduct('Ebics client PHP', 'de')
                            ->addOrderDetails(function (OrderDetailsBuilder $orderDetailsBuilder) use ($context) {
                                $this
                                    ->addOrderType($orderDetailsBuilder, 'BTD')
                                    ->addBTDOrderParams(
                                        $context->getBTFContext(),
                                        $context->getStartDateTime(),
                                        $context->getEndDateTime()
                                    );
                            })
                            ->addBankPubKeyDigests(
                                $context->getKeyRing()->getBankSignatureXVersion(),
                                $this->digestResolver->digest($context->getKeyRing()->getBankSignatureX()),
                                $context->getKeyRing()->getBankSignatureEVersion(),
                                $this->digestResolver->digest($context->getKeyRing()->getBankSignatureE())
                            )
                            ->addSecurityMedium(StaticBuilder::SECURITY_MEDIUM_0000);
                    })->addMutable(function (MutableBuilder $builder) use ($context) {
                        $builder
                            ->addTransactionPhase(MutableBuilder::PHASE_INITIALIZATION)
                            ->addSegmentNumber($context->getSegmentNumber(), $context->getIsLastSegment());
                    });
                })->addBody();
            })
            ->popInstance();

        $this->authSignatureHandler->handle($request);

        return $request;
    }

    public function createBTU(
        BTUContext $btuContext,
        DateTimeInterface $dateTime,
        UploadTransaction $transaction
    ): Request {
        $signatureData = new UserSignature();

        $this->userSignatureHandler->handle($signatureData, $transaction->getDigest());

        $signatureVersion = $this->keyRing->getUserSignatureAVersion();
        $dataDigest = $this->cryptService->sign(
            $this->keyRing->getUserSignatureA()->getPrivateKey(),
            $this->keyRing->getPassword(),
            $this->keyRing->getUserSignatureAVersion(),
            $transaction->getDigest()
        );

        $context = (new RequestContext())
            ->setBank($this->bank)
            ->setUser($this->user)
            ->setKeyRing($this->keyRing)
            ->setDateTime($dateTime)
            ->setBTUContext($btuContext)
            ->setTransactionKey($transaction->getKey())
            ->setNumSegments($transaction->getNumSegments())
            ->setSignatureData($signatureData)
            ->setSignatureVersion($signatureVersion)
            ->setDataDigest($dataDigest);

        $request = $this
            ->createRequestBuilderInstance()
            ->addContainerSecured(function (XmlBuilder $builder) use ($context) {
                $builder->addHeader(function (HeaderBuilder $builder) use ($context) {
                    $builder->addStatic(function (StaticBuilder $builder) use ($context) {
                        $builder
                            ->addHostId($context->getBank()->getHostId())
                            ->addRandomNonce()
                            ->addTimestamp($context->getDateTime())
                            ->addPartnerId($context->getUser()->getPartnerId())
                            ->addUserId($context->getUser()->getUserId())
                            ->addProduct('Ebics client PHP', 'de')
                            ->addOrderDetails(function (OrderDetailsBuilder $orderDetailsBuilder) use ($context) {
                                $this
                                    ->addOrderType($orderDetailsBuilder, 'BTU')
                                    ->addBTUOrderParams($context->getBTUContext());
                            })
                            ->addBankPubKeyDigests(
                                $context->getKeyRing()->getBankSignatureXVersion(),
                                $this->digestResolver->digest($context->getKeyRing()->getBankSignatureX()),
                                $context->getKeyRing()->getBankSignatureEVersion(),
                                $this->digestResolver->digest($context->getKeyRing()->getBankSignatureE())
                            )
                            ->addSecurityMedium(StaticBuilder::SECURITY_MEDIUM_0000)
                            ->addNumSegments($context->getNumSegments());
                    })->addMutable(function (MutableBuilder $builder) {
                        $builder->addTransactionPhase(MutableBuilder::PHASE_INITIALIZATION);
                    });
                })->addBody(function (BodyBuilder $builder) use ($context) {
                    $builder->addDataTransfer(function (DataTransferBuilder $builder) use ($context) {
                        $builder
                            ->addDataEncryptionInfo(function (DataEncryptionInfoBuilder $builder) use ($context) {
                                $builder
                                    ->addEncryptionPubKeyDigest($context->getKeyRing())
                                    ->addTransactionKey($context->getTransactionKey(), $context->getKeyRing());
                            })
                            ->addSignatureData($context->getSignatureData(), $context->getTransactionKey())
                            ->addDataDigest(
                                $context->getSignatureVersion(),
                                $context->getDataDigest()
                            )
                            ->addAdditionalOrderInfo();
                    });
                });
            })
            ->popInstance();

        $this->authSignatureHandler->handle($request);

        return $request;
    }

    public function createVMK(
        DateTimeInterface $dateTime,
        DateTimeInterface $startDateTime = null,
        DateTimeInterface $endDateTime = null,
        int $segmentNumber = null,
        bool $isLastSegment = null
    ): Request {
        throw new LogicException('Method not implemented yet for EBICS 3.0');
    }

    public function createSTA(
        DateTimeInterface $dateTime,
        DateTimeInterface $startDateTime = null,
        DateTimeInterface $endDateTime = null,
        int $segmentNumber = null,
        bool $isLastSegment = null
    ): Request {
        throw new LogicException('Method not implemented yet for EBICS 3.0');
    }

    public function createC52(
        DateTimeInterface $dateTime,
        DateTimeInterface $startDateTime = null,
        DateTimeInterface $endDateTime = null,
        int $segmentNumber = null,
        bool $isLastSegment = null
    ): Request {
        throw new LogicException('Method not implemented yet for EBICS 3.0');
    }

    public function createC53(
        DateTimeInterface $dateTime,
        DateTimeInterface $startDateTime = null,
        DateTimeInterface $endDateTime = null,
        int $segmentNumber = null,
        bool $isLastSegment = null
    ): Request {
        throw new LogicException('Method not implemented yet for EBICS 3.0');
    }

    public function createC54(
        DateTimeInterface $dateTime,
        DateTimeInterface $startDateTime = null,
        DateTimeInterface $endDateTime = null,
        int $segmentNumber = null,
        bool $isLastSegment = null
    ): Request {
        throw new LogicException('Method not implemented yet for EBICS 3.0');
    }

    public function createZ52(
        DateTimeInterface $dateTime,
        DateTimeInterface $startDateTime = null,
        DateTimeInterface $endDateTime = null,
        int $segmentNumber = null,
        bool $isLastSegment = null
    ): Request {
        throw new LogicException('Method not implemented yet for EBICS 3.0');
    }

    public function createZ53(
        DateTimeInterface $dateTime,
        DateTimeInterface $startDateTime = null,
        DateTimeInterface $endDateTime = null,
        int $segmentNumber = null,
        bool $isLastSegment = null
    ): Request {
        throw new LogicException('Method not implemented yet for EBICS 3.0');
    }

    /**
     * @throws EbicsException
     */
    public function createZ54(
        DateTimeInterface $dateTime,
        DateTimeInterface $startDateTime = null,
        DateTimeInterface $endDateTime = null,
        int $segmentNumber = null,
        bool $isLastSegment = null
    ): Request {
        $btfContext = new BTDContext();
        $btfContext->setServiceName('REP');
        $btfContext->setScope('CH');
        $btfContext->setMsgName('camt.054');
        $btfContext->setContainerFlag('ZIP');

        return $this->createBTD($dateTime, $btfContext, $startDateTime, $endDateTime, $segmentNumber, $isLastSegment);
    }

    /**
     * @throws EbicsException
     */
    public function createZSR(
        DateTimeInterface $dateTime,
        DateTimeInterface $startDateTime = null,
        DateTimeInterface $endDateTime = null,
        int $segmentNumber = null,
        bool $isLastSegment = null
    ): Request {
        $btfContext = new BTDContext();
        $btfContext->setServiceName('PSR');
        $btfContext->setScope('BIL');
        $btfContext->setMsgName('pain.002');
        $btfContext->setContainerFlag('ZIP');

        return $this->createBTD($dateTime, $btfContext, $startDateTime, $endDateTime, $segmentNumber, $isLastSegment);
    }

    public function createCCT(DateTimeInterface $dateTime, UploadTransaction $transaction): Request
    {
        throw new LogicException('Method not implemented yet for EBICS 3.0');
    }

    public function createCDD(DateTimeInterface $dateTime, UploadTransaction $transaction): Request
    {
        throw new LogicException('Method not implemented yet for EBICS 3.0');
    }

    public function createXE2(DateTimeInterface $dateTime, UploadTransaction $transaction): Request
    {
        throw new LogicException('Method not implemented yet for EBICS 3.0');
    }

    public function createYCT(DateTimeInterface $dateTime, UploadTransaction $transaction): Request
    {
        $btfContext = new BTUContext();
        $btfContext->setServiceName('MCT');
        $btfContext->setScope('BIL');
        $btfContext->setMsgName('pain.001');
        $btfContext->setFileName('yct.pain001.xml');

        return $this->createBTU($btfContext, $dateTime, $transaction);
    }
}
