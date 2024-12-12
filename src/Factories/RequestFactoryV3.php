<?php

namespace EbicsApi\Ebics\Factories;

use EbicsApi\Ebics\Builders\Request\BodyBuilder;
use EbicsApi\Ebics\Builders\Request\DataEncryptionInfoBuilder;
use EbicsApi\Ebics\Builders\Request\DataTransferBuilder;
use EbicsApi\Ebics\Builders\Request\HeaderBuilder;
use EbicsApi\Ebics\Builders\Request\MutableBuilder;
use EbicsApi\Ebics\Builders\Request\OrderDetailsBuilder;
use EbicsApi\Ebics\Builders\Request\RequestBuilder;
use EbicsApi\Ebics\Builders\Request\StaticBuilder;
use EbicsApi\Ebics\Builders\Request\XmlBuilder;
use EbicsApi\Ebics\Builders\Request\XmlBuilderV3;
use EbicsApi\Ebics\Contexts\BTDContext;
use EbicsApi\Ebics\Contexts\BTUContext;
use EbicsApi\Ebics\Contexts\RequestContext;
use EbicsApi\Ebics\Exceptions\EbicsException;
use EbicsApi\Ebics\Handlers\AuthSignatureHandlerV3;
use EbicsApi\Ebics\Handlers\OrderDataHandlerV3;
use EbicsApi\Ebics\Handlers\UserSignatureHandlerV3;
use EbicsApi\Ebics\Models\Bank;
use EbicsApi\Ebics\Models\Http\Request;
use EbicsApi\Ebics\Models\Keyring;
use EbicsApi\Ebics\Models\UploadTransaction;
use EbicsApi\Ebics\Models\User;
use EbicsApi\Ebics\Models\UserSignature;
use EbicsApi\Ebics\Services\DigestResolverV3;

/**
 * Ebics 3.0 RequestFactory.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
final class RequestFactoryV3 extends RequestFactory
{
    public function __construct(Bank $bank, User $user, Keyring $keyring)
    {
        $this->authSignatureHandler = new AuthSignatureHandlerV3($keyring);
        $this->userSignatureHandler = new UserSignatureHandlerV3($user, $keyring);
        $this->orderDataHandler = new OrderDataHandlerV3($user, $keyring);
        $this->digestResolver = new DigestResolverV3();
        parent::__construct($bank, $user, $keyring);
    }

    protected function createRequestBuilderInstance(): RequestBuilder
    {
        return $this->requestBuilder
            ->createInstance(function (Request $request) {
                return new XmlBuilderV3($request);
            });
    }

    protected function addOrderType(
        OrderDetailsBuilder $orderDetailsBuilder,
        string $orderType,
        bool $withES = false
    ): OrderDetailsBuilder {
        return $orderDetailsBuilder->addAdminOrderType($orderType);
    }

    /**
     * @throws EbicsException
     */
    public function createBTD(RequestContext $context): Request
    {
        $context
            ->setOrderType('BTD')
            ->setBank($this->bank)
            ->setUser($this->user)
            ->setKeyring($this->keyring);

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
                            ->addProduct($context->getProduct(), $context->getLanguage())
                            ->addOrderDetails(function (OrderDetailsBuilder $orderDetailsBuilder) use ($context) {
                                $this
                                    ->addOrderType($orderDetailsBuilder, $context->getOrderType())
                                    ->addBTDOrderParams(
                                        $context->getBTDContext(),
                                        $context->getStartDateTime(),
                                        $context->getEndDateTime()
                                    );
                            })
                            ->addBankPubKeyDigests(
                                $context->getKeyring()->getBankSignatureXVersion(),
                                $this->digestResolver->signDigest($context->getKeyring()->getBankSignatureX()),
                                $context->getKeyring()->getBankSignatureEVersion(),
                                $this->digestResolver->signDigest($context->getKeyring()->getBankSignatureE())
                            )
                            ->addSecurityMedium(StaticBuilder::SECURITY_MEDIUM_0000);
                    })->addMutable(function (MutableBuilder $builder) {
                        $builder
                            ->addTransactionPhase(MutableBuilder::PHASE_INITIALIZATION);
                    });
                })->addBody();
            })
            ->popInstance();

        $this->authSignatureHandler->handle($request);

        return $request;
    }

    public function createBTU(UploadTransaction $transaction, RequestContext $context): Request
    {
        $signatureData = new UserSignature();

        $this->userSignatureHandler->handle($signatureData, $transaction->getDigest());

        $signatureVersion = $this->keyring->getUserSignatureAVersion();
        $dataDigest = $this->cryptService->sign(
            $this->keyring->getUserSignatureA()->getPrivateKey(),
            $this->keyring->getPassword(),
            $this->keyring->getUserSignatureAVersion(),
            $transaction->getDigest()
        );

        $context
            ->setOrderType('BTU')
            ->setBank($this->bank)
            ->setUser($this->user)
            ->setKeyring($this->keyring)
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
                            ->addProduct($context->getProduct(), $context->getLanguage())
                            ->addOrderDetails(function (OrderDetailsBuilder $orderDetailsBuilder) use ($context) {
                                $this
                                    ->addOrderType($orderDetailsBuilder, $context->getOrderType())
                                    ->addBTUOrderParams($context->getBTUContext());
                            })
                            ->addBankPubKeyDigests(
                                $context->getKeyring()->getBankSignatureXVersion(),
                                $this->digestResolver->signDigest($context->getKeyring()->getBankSignatureX()),
                                $context->getKeyring()->getBankSignatureEVersion(),
                                $this->digestResolver->signDigest($context->getKeyring()->getBankSignatureE())
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
                                    ->addEncryptionPubKeyDigest($context->getKeyring())
                                    ->addTransactionKey($context->getTransactionKey(), $context->getKeyring());
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

    public function createVMK(RequestContext $context): Request
    {
        $btdContext = $context->getBTDContext();

        $btdContext->setServiceName('STM');
        $btdContext->setMsgName('mt942');

        return $this->createBTD($context);
    }

    public function createSTA(RequestContext $context): Request
    {
        $btdContext = $context->getBTDContext();

        $btdContext->setServiceName('EOP');
        $btdContext->setMsgName('mt940');

        return $this->createBTD($context);
    }

    public function createC52(RequestContext $context): Request
    {
        $btdContext = $context->getBTDContext();

        $btdContext->setServiceName('STM');
        $btdContext->setMsgName('camt.052');

        return $this->createBTD($context);
    }

    public function createC53(RequestContext $context): Request
    {
        $btdContext = $context->getBTDContext();

        $btdContext->setServiceName('EOP');
        $btdContext->setMsgName('camt.053');
        $btdContext->setContainerType('ZIP');

        return $this->createBTD($context);
    }

    public function createC54(RequestContext $context): Request
    {
        $btdContext = $context->getBTDContext();

        $btdContext->setServiceName('REP');
        $btdContext->setMsgName('camt.054');
        $btdContext->setContainerType('ZIP');

        return $this->createBTD($context);
    }

    public function createZ52(RequestContext $context): Request
    {
        $btdContext = $context->getBTDContext();

        $btdContext->setServiceName('STM');
        $btdContext->setMsgName('camt.052');
        $btdContext->setContainerType('ZIP');

        return $this->createBTD($context);
    }

    public function createZ53(RequestContext $context): Request
    {
        $btdContext = $context->getBTDContext();

        $btdContext->setServiceName('EOP');
        $btdContext->setMsgName('camt.053');
        $btdContext->setContainerType('ZIP');

        return $this->createBTD($context);
    }

    /**
     * @throws EbicsException
     */
    public function createZ54(RequestContext $context): Request
    {
        $btdContext = $context->getBTDContext();

        $btdContext->setServiceName('REP');
        $btdContext->setMsgName('camt.054');
        $btdContext->setContainerType('ZIP');
        $btdContext->setServiceOption('XQRR');

        return $this->createBTD($context);
    }

    /**
     * @throws EbicsException
     */
    public function createZSR(RequestContext $context): Request
    {
        $btdContext = $context->getBTDContext();

        $btdContext->setServiceName('PSR');
        $btdContext->setScope('BIL');
        $btdContext->setMsgName('pain.002');
        $btdContext->setContainerType('ZIP');

        $context->setBTDContext($btdContext);

        return $this->createBTD($context);
    }

    /**
     * @throws EbicsException
     */
    public function createXEK(RequestContext $context): Request
    {
        $btdContext = $context->getBTDContext();

        $btdContext->setServiceName('EOP');
        $btdContext->setMsgName('pdf');
        $btdContext->setContainerType('ZIP');

        $context->setBTDContext($btdContext);

        return $this->createBTD($context);
    }

    public function createCCT(UploadTransaction $transaction, RequestContext $context): Request
    {
        $btuContext = $context->getBTUContext();

        $btuContext->setServiceName('SCT');
        $btuContext->setMsgName('pain.001');
        $btuContext->setFileName('cct.pain.001.xxx.xml');

        return $this->createBTU($transaction, $context);
    }

    public function createCDD(UploadTransaction $transaction, RequestContext $context): Request
    {
        $btuContext = $context->getBTUContext();

        $btuContext->setServiceName('SDD');
        $btuContext->setScope('GLB');
        $btuContext->setMsgName('pain.008');
        $btuContext->setServiceOption('COR');
        $btuContext->setFileName('cdd.pain.008.xxx.xml');

        return $this->createBTU($transaction, $context);
    }

    public function createCDB(UploadTransaction $transaction, RequestContext $context): Request
    {
        $btuContext = $context->getBTUContext();

        $btuContext->setServiceName('SDD');
        $btuContext->setMsgName('pain.008');
        $btuContext->setServiceOption('B2B');
        $btuContext->setFileName('cdb.pain.008.xxx.xml');

        return $this->createBTU($transaction, $context);
    }

    public function createCIP(UploadTransaction $transaction, RequestContext $context): Request
    {
        $btuContext = $context->getBTUContext();

        $btuContext->setServiceName('SCI');
        $btuContext->setMsgName('pain.001');
        $btuContext->setFileName('cip.pain.001.xxx.xml');

        return $this->createBTU($transaction, $context);
    }

    public function createXE2(UploadTransaction $transaction, RequestContext $context): Request
    {
        $btuContext = $context->getBTUContext();

        $btuContext->setServiceName('MCT');
        $btuContext->setMsgName('pain.001');
        $btuContext->setFileName('xe2.pain.001.xxx.xml');

        return $this->createBTU($transaction, $context);
    }

    public function createXE3(UploadTransaction $transaction, RequestContext $context): Request
    {
        $btuContext = $context->getBTUContext();

        $btuContext->setServiceName('SDD');
        $btuContext->setMsgName('pain.008');
        $btuContext->setFileName('xe3.pain.008.xxx.xml');

        return $this->createBTU($transaction, $context);
    }

    public function createYCT(UploadTransaction $transaction, RequestContext $context): Request
    {
        $btuContext = $context->getBTUContext();

        $btuContext->setServiceName('MCT');
        $btuContext->setScope('BIL');
        $btuContext->setMsgName('pain.001');
        $btuContext->setFileName('yct.pain.001.xxx.xml');

        return $this->createBTU($transaction, $context);
    }

    public function prepareDownloadContext(RequestContext $requestContext = null): RequestContext
    {
        $requestContext = $this->prepareStandardContext($requestContext);
        if (null === $requestContext->getBTDContext()) {
            $btdContext = new BTDContext();
            $btdContext->setScope($this->bank->getCountryCode());
            $requestContext->setBTDContext($btdContext);
        }

        return $requestContext;
    }

    public function prepareUploadContext(RequestContext $requestContext = null): RequestContext
    {
        $requestContext = $this->prepareStandardContext($requestContext);
        if (null === $requestContext->getBTUContext()) {
            $btuContext = new BTUContext();
            $btuContext->setScope($this->bank->getCountryCode());
            $requestContext->setBTUContext($btuContext);
        }

        return $requestContext;
    }
}
