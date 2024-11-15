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
use AndrewSvirin\Ebics\Builders\Request\TransferReceiptBuilder;
use AndrewSvirin\Ebics\Builders\Request\XmlBuilder;
use AndrewSvirin\Ebics\Contexts\RequestContext;
use AndrewSvirin\Ebics\Contracts\SignatureInterface;
use AndrewSvirin\Ebics\Exceptions\EbicsException;
use AndrewSvirin\Ebics\Handlers\AuthSignatureHandler;
use AndrewSvirin\Ebics\Handlers\OrderDataHandler;
use AndrewSvirin\Ebics\Handlers\UserSignatureHandler;
use AndrewSvirin\Ebics\Models\Bank;
use AndrewSvirin\Ebics\Models\CustomerH3K;
use AndrewSvirin\Ebics\Models\CustomerHIA;
use AndrewSvirin\Ebics\Models\CustomerINI;
use AndrewSvirin\Ebics\Models\Http\Request;
use AndrewSvirin\Ebics\Models\Keyring;
use AndrewSvirin\Ebics\Models\UploadTransaction;
use AndrewSvirin\Ebics\Models\User;
use AndrewSvirin\Ebics\Models\UserSignature;
use AndrewSvirin\Ebics\Services\CryptService;
use AndrewSvirin\Ebics\Services\DigestResolver;

/**
 * Class RequestFactory represents producers for the @see Request.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
abstract class RequestFactory
{
    protected RequestBuilder $requestBuilder;
    protected OrderDataHandler $orderDataHandler;
    protected DigestResolver $digestResolver;
    protected AuthSignatureHandler $authSignatureHandler;
    protected UserSignatureHandler $userSignatureHandler;
    protected CryptService $cryptService;
    protected Bank $bank;
    protected User $user;
    protected Keyring $keyring;

    /**
     * Constructor.
     *
     * @param Bank $bank
     * @param User $user
     * @param Keyring $keyring
     */
    public function __construct(Bank $bank, User $user, Keyring $keyring)
    {
        $this->requestBuilder = new RequestBuilder();
        $this->cryptService = new CryptService();
        $this->bank = $bank;
        $this->user = $user;
        $this->keyring = $keyring;
    }

    abstract protected function createRequestBuilderInstance(): RequestBuilder;

    abstract protected function addOrderType(
        OrderDetailsBuilder $orderDetailsBuilder,
        string $orderType,
        bool $withES = false
    ): OrderDetailsBuilder;

    public function createHEV(RequestContext $context): Request
    {
        $context->setBank($this->bank);

        $request = $this
            ->createRequestBuilderInstance()
            ->addContainerHEV(function (XmlBuilder $builder) use ($context) {
                $builder->addHostId($context->getBank()->getHostId());
            })
            ->popInstance();

        return $request;
    }

    public function createINI(SignatureInterface $certificateA, RequestContext $context): Request
    {
        $orderData = new CustomerINI();
        $this->orderDataHandler->handleINI(
            $orderData,
            $certificateA,
            $context->getDateTime()
        );

        $context
            ->setOrderType('INI')
            ->setBank($this->bank)
            ->setUser($this->user)
            ->setOrderData($orderData->getContent());

        $request = $this
            ->createRequestBuilderInstance()
            ->addContainerUnsecured(function (XmlBuilder $builder) use ($context) {
                $builder->addHeader(function (HeaderBuilder $builder) use ($context) {
                    $builder->addStatic(function (StaticBuilder $builder) use ($context) {
                        $builder
                            ->addHostId($context->getBank()->getHostId())
                            ->addPartnerId($context->getUser()->getPartnerId())
                            ->addUserId($context->getUser()->getUserId())
                            ->addProduct($context->getProduct(), $context->getLanguage())
                            ->addOrderDetails(function (OrderDetailsBuilder $orderDetailsBuilder) use ($context) {
                                $this->addOrderType($orderDetailsBuilder, $context->getOrderType());
                            })
                            ->addSecurityMedium(StaticBuilder::SECURITY_MEDIUM_0000);
                    })->addMutable();
                })->addBody(function (BodyBuilder $builder) use ($context) {
                    $builder->addDataTransfer(function (DataTransferBuilder $builder) use ($context) {
                        $builder->addOrderData($context->getOrderData());
                    });
                });
            })
            ->popInstance();

        return $request;
    }

    public function createHIA(
        SignatureInterface $certificateE,
        SignatureInterface $certificateX,
        RequestContext $context
    ): Request {
        $orderData = new CustomerHIA();
        $this->orderDataHandler->handleHIA(
            $orderData,
            $certificateE,
            $certificateX,
            $context->getDateTime()
        );

        $context
            ->setOrderType('HIA')
            ->setBank($this->bank)
            ->setUser($this->user)
            ->setOrderData($orderData->getContent());

        $request = $this
            ->createRequestBuilderInstance()
            ->addContainerUnsecured(function (XmlBuilder $builder) use ($context) {
                $builder->addHeader(function (HeaderBuilder $builder) use ($context) {
                    $builder->addStatic(function (StaticBuilder $builder) use ($context) {
                        $builder
                            ->addHostId($context->getBank()->getHostId())
                            ->addPartnerId($context->getUser()->getPartnerId())
                            ->addUserId($context->getUser()->getUserId())
                            ->addProduct($context->getProduct(), $context->getLanguage())
                            ->addOrderDetails(function (OrderDetailsBuilder $orderDetailsBuilder) use ($context) {
                                $this->addOrderType($orderDetailsBuilder, $context->getOrderType());
                            })
                            ->addSecurityMedium(StaticBuilder::SECURITY_MEDIUM_0000);
                    })->addMutable();
                })->addBody(function (BodyBuilder $builder) use ($context) {
                    $builder->addDataTransfer(function (DataTransferBuilder $builder) use ($context) {
                        $builder->addOrderData($context->getOrderData());
                    });
                });
            })
            ->popInstance();

        return $request;
    }

    public function createH3K(
        SignatureInterface $certificateA,
        SignatureInterface $certificateE,
        SignatureInterface $certificateX,
        RequestContext $context
    ): Request {
        $orderData = new CustomerH3K();
        $this->orderDataHandler->handleH3K(
            $orderData,
            $certificateA,
            $certificateE,
            $certificateX
        );

        $signatureData = new UserSignature();
        $this->userSignatureHandler->handle(
            $signatureData,
            $this->cryptService->hash($orderData->getContent())
        );

        $context
            ->setOrderType('H3K')
            ->setKeyring($this->keyring)
            ->setBank($this->bank)
            ->setUser($this->user)
            ->setOrderData($orderData->getContent())
            ->setSignatureData($signatureData);

        $request = $this
            ->createRequestBuilderInstance()
            ->addContainerUnsigned(function (XmlBuilder $builder) use ($context) {
                $builder->addHeader(function (HeaderBuilder $builder) use ($context) {
                    $builder->addStatic(function (StaticBuilder $builder) use ($context) {
                        $builder
                            ->addHostId($context->getBank()->getHostId())
                            ->addPartnerId($context->getUser()->getPartnerId())
                            ->addUserId($context->getUser()->getUserId())
                            ->addProduct($context->getProduct(), $context->getLanguage())
                            ->addOrderDetails(function (OrderDetailsBuilder $orderDetailsBuilder) use ($context) {
                                $this->addOrderType($orderDetailsBuilder, $context->getOrderType());
                            })
                            ->addSecurityMedium(StaticBuilder::SECURITY_MEDIUM_0000);
                    })->addMutable();
                })->addBody(function (BodyBuilder $builder) use ($context) {
                    $builder->addDataTransfer(function (DataTransferBuilder $builder) use ($context) {
                        $builder->addSignatureData($context->getSignatureData(), '');
                        $builder->addOrderData($context->getOrderData());
                    });
                });
            })
            ->popInstance();

        return $request;
    }

    /**
     * @throws EbicsException
     */
    public function createHPB(RequestContext $context): Request
    {
        $context
            ->setOrderType('HPB')
            ->setBank($this->bank)
            ->setUser($this->user);

        $request = $this
            ->createRequestBuilderInstance()
            ->addContainerSecuredNoPubKeyDigests(function (XmlBuilder $builder) use ($context) {
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
                                $this->addOrderType($orderDetailsBuilder, $context->getOrderType());
                            })
                            ->addSecurityMedium(StaticBuilder::SECURITY_MEDIUM_0000);
                    })->addMutable();
                })->addBody();
            })
            ->popInstance();

        $this->authSignatureHandler->handle($request);

        return $request;
    }

    public function createSPR(UploadTransaction $transaction, RequestContext $context): Request
    {
        $signatureData = new UserSignature();
        $this->userSignatureHandler->handle($signatureData, $transaction->getDigest());

        $context
            ->setOrderType('SPR')
            ->setBank($this->bank)
            ->setUser($this->user)
            ->setKeyring($this->keyring)
            ->setTransactionKey($transaction->getKey())
            ->setNumSegments($transaction->getNumSegments())
            ->setSignatureData($signatureData);

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
                            ->addSignatureData($context->getSignatureData(), $context->getTransactionKey())
                            ->addDataDigest($context->getKeyring()->getUserSignatureAVersion());
                    });
                });
            })
            ->popInstance();

        $this->authSignatureHandler->handle($request);

        return $request;
    }

    /**
     * @throws EbicsException
     */
    public function createHPD(RequestContext $context): Request
    {
        $context
            ->setOrderType('HPD')
            ->setBank($this->bank)
            ->setUser($this->user)
            ->setKeyring($this->keyring);

        return $this->buildStandardRequest($context);
    }

    /**
     * @throws EbicsException
     */
    public function createHKD(RequestContext $context): Request
    {
        $context
            ->setOrderType('HKD')
            ->setBank($this->bank)
            ->setUser($this->user)
            ->setKeyring($this->keyring);

        return $this->buildStandardRequest($context);
    }

    /**
     * @throws EbicsException
     */
    public function createHTD(RequestContext $context): Request
    {
        $context
            ->setOrderType('HTD')
            ->setBank($this->bank)
            ->setUser($this->user)
            ->setKeyring($this->keyring);

        return $this->buildStandardRequest($context);
    }

    /**
     * @throws EbicsException
     */
    public function createHAA(RequestContext $context): Request
    {
        $context
            ->setOrderType('HAA')
            ->setBank($this->bank)
            ->setUser($this->user)
            ->setKeyring($this->keyring);

        return $this->buildStandardRequest($context);
    }

    /**
     * @throws EbicsException
     */
    private function buildStandardRequest(RequestContext $context): Request
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
                                    ->addOrderType($orderDetailsBuilder, $context->getOrderType())
                                    ->addStandardOrderParams();
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
    public function createPTK(RequestContext $context): Request
    {
        $context
            ->setOrderType('PTK')
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
    public function createFDL(RequestContext $context): Request
    {
        $context
            ->setOrderType('FDL')
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
                                    ->addOrderType(
                                        $orderDetailsBuilder,
                                        $context->getOrderType(),
                                        $context->getWithES()
                                    )
                                    ->addFDLOrderParams(
                                        $context->getFdlContext(),
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

    /**
     * @throws EbicsException
     */
    public function createFUL(UploadTransaction $transaction, RequestContext $context): Request
    {
        $signatureData = new UserSignature();
        $this->userSignatureHandler->handle($signatureData, $transaction->getDigest());

        $context
            ->setOrderType('FUL')
            ->setBank($this->bank)
            ->setUser($this->user)
            ->setKeyring($this->keyring)
            ->setTransactionKey($transaction->getKey())
            ->setNumSegments($transaction->getNumSegments())
            ->setSignatureData($signatureData);

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
                                    ->addFULOrderParams($context->getFulContext());
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

    abstract public function createBTD(RequestContext $context): Request;

    abstract public function createBTU(UploadTransaction $transaction, RequestContext $context): Request;

    /**
     * @throws EbicsException
     */
    public function createTransferReceipt(string $transactionId, bool $acknowledged): Request
    {
        $context = (new RequestContext())
            ->setBank($this->bank)
            ->setTransactionId($transactionId)
            ->setReceiptCode(true === $acknowledged ?
                TransferReceiptBuilder::CODE_RECEIPT_POSITIVE : TransferReceiptBuilder::CODE_RECEIPT_NEGATIVE);

        $request = $this
            ->createRequestBuilderInstance()
            ->addContainerSecured(function (XmlBuilder $builder) use ($context) {
                $builder->addHeader(function (HeaderBuilder $builder) use ($context) {
                    $builder->addStatic(function (StaticBuilder $builder) use ($context) {
                        $builder
                            ->addHostId($context->getBank()->getHostId())
                            ->addTransactionId($context->getTransactionId());
                    })->addMutable(function (MutableBuilder $builder) {
                        $builder->addTransactionPhase(MutableBuilder::PHASE_RECEIPT);
                    });
                })->addBody(function (BodyBuilder $builder) use ($context) {
                    $builder->addTransferReceipt(function (TransferReceiptBuilder $builder) use ($context) {
                        $builder->addReceiptCode($context->getReceiptCode());
                    });
                });
            })
            ->popInstance();

        $this->authSignatureHandler->handle($request);

        return $request;
    }

    /**
     * @throws EbicsException
     */
    public function createTransferUpload(
        string $transactionId,
        string $transactionKey,
        string $orderData,
        int $segmentNumber,
        bool $isLastSegment = null
    ): Request {
        $context = (new RequestContext())
            ->setBank($this->bank)
            ->setTransactionId($transactionId)
            ->setTransactionKey($transactionKey)
            ->setOrderData($orderData)
            ->setSegmentNumber($segmentNumber)
            ->setIsLastSegment($isLastSegment);

        $request = $this
            ->createRequestBuilderInstance()
            ->addContainerSecured(function (XmlBuilder $builder) use ($context) {
                $builder->addHeader(function (HeaderBuilder $builder) use ($context) {
                    $builder->addStatic(function (StaticBuilder $builder) use ($context) {
                        $builder
                            ->addHostId($context->getBank()->getHostId())
                            ->addTransactionId($context->getTransactionId());
                    })->addMutable(function (MutableBuilder $builder) use ($context) {
                        $builder
                            ->addTransactionPhase(MutableBuilder::PHASE_TRANSFER)
                            ->addSegmentNumber($context->getSegmentNumber(), $context->getIsLastSegment());
                    });
                })->addBody(function (BodyBuilder $builder) use ($context) {
                    $builder->addDataTransfer(function (DataTransferBuilder $builder) use ($context) {
                        $builder->addOrderData($context->getOrderData(), $context->getTransactionKey());
                    });
                });
            })
            ->popInstance();

        $this->authSignatureHandler->handle($request);

        return $request;
    }

    /**
     * @throws EbicsException
     */
    public function createTransferDownload(
        string $transactionId,
        int $segmentNumber,
        bool $isLastSegment = null
    ): Request {
        $context = (new RequestContext())
            ->setBank($this->bank)
            ->setTransactionId($transactionId)
            ->setSegmentNumber($segmentNumber)
            ->setIsLastSegment($isLastSegment);

        $request = $this
            ->createRequestBuilderInstance()
            ->addContainerSecured(function (XmlBuilder $builder) use ($context) {
                $builder->addHeader(function (HeaderBuilder $builder) use ($context) {
                    $builder->addStatic(function (StaticBuilder $builder) use ($context) {
                        $builder
                            ->addHostId($context->getBank()->getHostId())
                            ->addTransactionId($context->getTransactionId());
                    })->addMutable(function (MutableBuilder $builder) use ($context) {
                        $builder
                            ->addTransactionPhase(MutableBuilder::PHASE_TRANSFER)
                            ->addSegmentNumber($context->getSegmentNumber(), $context->getIsLastSegment());
                    });
                })->addBody();
            })
            ->popInstance();

        $this->authSignatureHandler->handle($request);

        return $request;
    }

    abstract public function createVMK(RequestContext $context): Request;

    abstract public function createSTA(RequestContext $context): Request;

    abstract public function createC52(RequestContext $context): Request;

    abstract public function createC53(RequestContext $context): Request;

    abstract public function createC54(RequestContext $context): Request;

    abstract public function createZ52(RequestContext $context): Request;

    abstract public function createZ53(RequestContext $context): Request;

    abstract public function createZ54(RequestContext $context): Request;

    abstract public function createZSR(RequestContext $context): Request;

    abstract public function createXEK(RequestContext $context): Request;

    abstract public function createCCT(UploadTransaction $transaction, RequestContext $context): Request;

    abstract public function createCDD(UploadTransaction $transaction, RequestContext $context): Request;

    abstract public function createCDB(UploadTransaction $transaction, RequestContext $context): Request;

    abstract public function createCIP(UploadTransaction $transaction, RequestContext $context): Request;

    abstract public function createXE2(UploadTransaction $transaction, RequestContext $context): Request;

    abstract public function createXE3(UploadTransaction $transaction, RequestContext $context): Request;

    abstract public function createYCT(UploadTransaction $transaction, RequestContext $context): Request;

    /**
     * @throws EbicsException
     */
    public function createHVU(RequestContext $context): Request
    {
        $context
            ->setOrderType('HVU')
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
                                    ->addHVUOrderParams();
                            })
                            ->addBankPubKeyDigests(
                                $context->getKeyring()->getBankSignatureXVersion(),
                                $this->digestResolver->signDigest($context->getKeyring()->getBankSignatureX()),
                                $context->getKeyring()->getBankSignatureEVersion(),
                                $this->digestResolver->signDigest($context->getKeyring()->getBankSignatureE())
                            )
                            ->addSecurityMedium(StaticBuilder::SECURITY_MEDIUM_0200);
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
    public function createHVZ(RequestContext $context): Request
    {
        $context
            ->setOrderType('HVZ')
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
                                    ->addHVZOrderParams();
                            })
                            ->addBankPubKeyDigests(
                                $context->getKeyring()->getBankSignatureXVersion(),
                                $this->digestResolver->signDigest($context->getKeyring()->getBankSignatureX()),
                                $context->getKeyring()->getBankSignatureEVersion(),
                                $this->digestResolver->signDigest($context->getKeyring()->getBankSignatureE())
                            )
                            ->addSecurityMedium(StaticBuilder::SECURITY_MEDIUM_0200);
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
    public function createHVE(UploadTransaction $transaction, RequestContext $context): Request
    {
        $signatureData = new UserSignature();
        $this->userSignatureHandler->handle($signatureData, $transaction->getDigest());

        $context
            ->setOrderType('HVE')
            ->setBank($this->bank)
            ->setUser($this->user)
            ->setKeyring($this->keyring)
            ->setTransactionKey($transaction->getKey())
            ->setNumSegments($transaction->getNumSegments())
            ->setSignatureData($signatureData);

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
                                    ->addHVEOrderParams($context->getHVEContext());
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
                            ->addDataDigest($context->getKeyring()->getUserSignatureAVersion());
                    });
                });
            })
            ->popInstance();

        $this->authSignatureHandler->handle($request);

        return $request;
    }

    public function createHVD(RequestContext $context): Request
    {
        $context
            ->setOrderType('HVD')
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
                                    ->addHVDOrderParams($context->getHVDContext());
                            })
                            ->addBankPubKeyDigests(
                                $context->getKeyring()->getBankSignatureXVersion(),
                                $this->digestResolver->signDigest($context->getKeyring()->getBankSignatureX()),
                                $context->getKeyring()->getBankSignatureEVersion(),
                                $this->digestResolver->signDigest($context->getKeyring()->getBankSignatureE())
                            )
                            ->addSecurityMedium(StaticBuilder::SECURITY_MEDIUM_0200);
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

    public function createHVT(RequestContext $context): Request
    {
        $context
            ->setOrderType('HVT')
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
                                    ->addHVTOrderParams($context->getHVTContext());
                            })
                            ->addBankPubKeyDigests(
                                $context->getKeyring()->getBankSignatureXVersion(),
                                $this->digestResolver->signDigest($context->getKeyring()->getBankSignatureX()),
                                $context->getKeyring()->getBankSignatureEVersion(),
                                $this->digestResolver->signDigest($context->getKeyring()->getBankSignatureE())
                            )
                            ->addSecurityMedium(StaticBuilder::SECURITY_MEDIUM_0200);
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

    public function prepareStandardContext(RequestContext $requestContext = null): RequestContext
    {
        if (null === $requestContext) {
            $requestContext = new RequestContext();
        }

        return $requestContext;
    }

    abstract public function prepareDownloadContext(RequestContext $requestContext = null): RequestContext;

    abstract public function prepareUploadContext(RequestContext $requestContext = null): RequestContext;
}
