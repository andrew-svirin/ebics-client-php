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
use EbicsApi\Ebics\Builders\Request\XmlBuilderV25;
use EbicsApi\Ebics\Contexts\RequestContext;
use EbicsApi\Ebics\Exceptions\EbicsException;
use EbicsApi\Ebics\Handlers\AuthSignatureHandlerV25;
use EbicsApi\Ebics\Handlers\OrderDataHandlerV25;
use EbicsApi\Ebics\Handlers\UserSignatureHandlerV2;
use EbicsApi\Ebics\Models\Bank;
use EbicsApi\Ebics\Models\Http\Request;
use EbicsApi\Ebics\Models\Keyring;
use EbicsApi\Ebics\Models\UploadTransaction;
use EbicsApi\Ebics\Models\User;
use EbicsApi\Ebics\Models\UserSignature;
use EbicsApi\Ebics\Services\DigestResolverV2;
use LogicException;

/**
 * Ebics 2.5 RequestFactory.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
final class RequestFactoryV25 extends RequestFactoryV2
{
    public function __construct(Bank $bank, User $user, Keyring $keyring)
    {
        $this->authSignatureHandler = new AuthSignatureHandlerV25($keyring);
        $this->userSignatureHandler = new UserSignatureHandlerV2($user, $keyring);
        $this->orderDataHandler = new OrderDataHandlerV25($user, $keyring);
        $this->digestResolver = new DigestResolverV2();
        parent::__construct($bank, $user, $keyring);
    }

    protected function createRequestBuilderInstance(): RequestBuilder
    {
        return $this->requestBuilder
            ->createInstance(function (Request $request) {
                return new XmlBuilderV25($request);
            });
    }

    protected function addOrderType(
        OrderDetailsBuilder $orderDetailsBuilder,
        string $orderType,
        bool $withES = false
    ): OrderDetailsBuilder {
        switch ($orderType) {
            case 'INI':
            case 'HIA':
                $orderAttribute = OrderDetailsBuilder::ORDER_ATTRIBUTE_DZNNN;
                break;
            case 'CCT':
            case 'CDD':
            case 'CDB':
            case 'CIP':
            case 'XE2':
            case 'XE3':
                $orderAttribute = $withES ?
                    OrderDetailsBuilder::ORDER_ATTRIBUTE_OZHNN : OrderDetailsBuilder::ORDER_ATTRIBUTE_DZHNN;
                break;
            case 'HVE':
            case 'SPR':
                $orderAttribute = OrderDetailsBuilder::ORDER_ATTRIBUTE_UZHNN;
                break;
            default:
                $orderAttribute = $withES ?
                    OrderDetailsBuilder::ORDER_ATTRIBUTE_OZHNN : OrderDetailsBuilder::ORDER_ATTRIBUTE_DZHNN;
        }

        return $orderDetailsBuilder
            ->addOrderType($orderType)
            ->addOrderAttribute($orderAttribute);
    }

    /**
     * @throws EbicsException
     */
    public function createVMK(RequestContext $context): Request
    {
        $context
            ->setOrderType('VMK')
            ->setBank($this->bank)
            ->setUser($this->user)
            ->setKeyring($this->keyring);

        return $this->buildDownloadRequest($context);
    }

    /**
     * @throws EbicsException
     */
    public function createSTA(RequestContext $context): Request
    {
        $context
            ->setOrderType('STA')
            ->setBank($this->bank)
            ->setUser($this->user)
            ->setKeyring($this->keyring);

        return $this->buildDownloadRequest($context);
    }

    /**
     * @throws EbicsException
     */
    public function createC52(RequestContext $context): Request
    {
        $context
            ->setOrderType('C52')
            ->setBank($this->bank)
            ->setUser($this->user)
            ->setKeyring($this->keyring);

        return $this->buildDownloadRequest($context);
    }

    /**
     * @throws EbicsException
     */
    public function createC53(RequestContext $context): Request
    {
        $context
            ->setOrderType('C53')
            ->setBank($this->bank)
            ->setUser($this->user)
            ->setKeyring($this->keyring);

        return $this->buildDownloadRequest($context);
    }

    /**
     * @throws EbicsException
     */
    public function createC54(RequestContext $context): Request
    {
        $context
            ->setOrderType('C54')
            ->setBank($this->bank)
            ->setUser($this->user)
            ->setKeyring($this->keyring);

        return $this->buildDownloadRequest($context);
    }

    /**
     * @throws EbicsException
     */
    public function createZ52(RequestContext $context): Request
    {
        $context
            ->setOrderType('Z52')
            ->setBank($this->bank)
            ->setUser($this->user)
            ->setKeyring($this->keyring);

        return $this->buildDownloadRequest($context);
    }

    /**
     * @throws EbicsException
     */
    public function createZ53(RequestContext $context): Request
    {
        $context
            ->setOrderType('Z53')
            ->setBank($this->bank)
            ->setUser($this->user)
            ->setKeyring($this->keyring);

        return $this->buildDownloadRequest($context);
    }

    /**
     * @throws EbicsException
     */
    public function createZ54(RequestContext $context): Request
    {
        $context
            ->setOrderType('Z54')
            ->setBank($this->bank)
            ->setUser($this->user)
            ->setKeyring($this->keyring);

        return $this->buildDownloadRequest($context);
    }

    public function createZSR(RequestContext $context): Request
    {
        throw new LogicException('Method not implemented yet for EBICS 2.5');
    }

    public function createXEK(RequestContext $context): Request
    {
        throw new LogicException('Method not implemented yet for EBICS 2.5');
    }

    /**
     * @throws EbicsException
     */
    public function createCCT(UploadTransaction $transaction, RequestContext $context): Request
    {
        $signatureData = new UserSignature();
        $this->userSignatureHandler->handle($signatureData, $transaction->getDigest());

        $context
            ->setOrderType('CCT')
            ->setBank($this->bank)
            ->setUser($this->user)
            ->setKeyring($this->keyring)
            ->setTransactionKey($transaction->getKey())
            ->setNumSegments($transaction->getNumSegments())
            ->setSignatureData($signatureData);

        return $this->buildUploadRequest($context);
    }

    /**
     * @throws EbicsException
     */
    public function createCDD(UploadTransaction $transaction, RequestContext $context): Request
    {
        $signatureData = new UserSignature();
        $this->userSignatureHandler->handle($signatureData, $transaction->getDigest());

        $context
            ->setOrderType('CDD')
            ->setBank($this->bank)
            ->setUser($this->user)
            ->setKeyring($this->keyring)
            ->setTransactionKey($transaction->getKey())
            ->setNumSegments($transaction->getNumSegments())
            ->setSignatureData($signatureData);

        return $this->buildUploadRequest($context);
    }

    /**
     * @throws EbicsException
     */
    public function createCDB(UploadTransaction $transaction, RequestContext $context): Request
    {
        $signatureData = new UserSignature();
        $this->userSignatureHandler->handle($signatureData, $transaction->getDigest());

        $context
            ->setOrderType('CDB')
            ->setBank($this->bank)
            ->setUser($this->user)
            ->setKeyring($this->keyring)
            ->setTransactionKey($transaction->getKey())
            ->setNumSegments($transaction->getNumSegments())
            ->setSignatureData($signatureData);

        return $this->buildUploadRequest($context);
    }

    /**
     * @throws EbicsException
     */
    public function createCIP(UploadTransaction $transaction, RequestContext $context): Request
    {
        $signatureData = new UserSignature();
        $this->userSignatureHandler->handle($signatureData, $transaction->getDigest());

        $context
            ->setOrderType('CIP')
            ->setBank($this->bank)
            ->setUser($this->user)
            ->setKeyring($this->keyring)
            ->setTransactionKey($transaction->getKey())
            ->setNumSegments($transaction->getNumSegments())
            ->setSignatureData($signatureData);

        return $this->buildUploadRequest($context);
    }

    /**
     * @throws EbicsException
     */
    public function createXE2(UploadTransaction $transaction, RequestContext $context): Request
    {
        $signatureData = new UserSignature();
        $this->userSignatureHandler->handle($signatureData, $transaction->getDigest());

        $context
            ->setOrderType('XE2')
            ->setBank($this->bank)
            ->setUser($this->user)
            ->setKeyring($this->keyring)
            ->setTransactionKey($transaction->getKey())
            ->setNumSegments($transaction->getNumSegments())
            ->setSignatureData($signatureData);

        return $this->buildUploadRequest($context);
    }

    /**
     * @throws EbicsException
     */
    public function createXE3(UploadTransaction $transaction, RequestContext $context): Request
    {
        $signatureData = new UserSignature();
        $this->userSignatureHandler->handle($signatureData, $transaction->getDigest());

        $context
            ->setOrderType('XE3')
            ->setBank($this->bank)
            ->setUser($this->user)
            ->setKeyring($this->keyring)
            ->setTransactionKey($transaction->getKey())
            ->setNumSegments($transaction->getNumSegments())
            ->setSignatureData($signatureData);

        return $this->buildUploadRequest($context);
    }

    /**
     * @throws EbicsException
     */
    private function buildDownloadRequest(RequestContext $context): Request
    {
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
                                    ->addOrderType(
                                        $orderDetailsBuilder,
                                        $context->getOrderType(),
                                        $context->getWithES()
                                    )
                                    ->addStandardOrderParams($context->getStartDateTime(), $context->getEndDateTime());
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

    /**
     * @throws EbicsException
     */
    private function buildUploadRequest(RequestContext $context): Request
    {
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
                                    ->addOrderType(
                                        $orderDetailsBuilder,
                                        $context->getOrderType(),
                                        $context->getWithES()
                                    )
                                    ->addStandardOrderParams();
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
                            ->addSignatureData($context->getSignatureData(), $context->getTransactionKey());
                    });
                });
            })
            ->popInstance();

        $this->authSignatureHandler->handle($request);

        return $request;
    }

    public function createYCT(UploadTransaction $transaction, RequestContext $context): Request
    {
        throw new LogicException('Method not implemented yet for EBICS 2.5');
    }
}
