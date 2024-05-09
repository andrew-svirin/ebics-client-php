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
use AndrewSvirin\Ebics\Builders\Request\XmlBuilderV25;
use AndrewSvirin\Ebics\Contexts\RequestContext;
use AndrewSvirin\Ebics\Exceptions\EbicsException;
use AndrewSvirin\Ebics\Handlers\AuthSignatureHandlerV25;
use AndrewSvirin\Ebics\Handlers\OrderDataHandlerV25;
use AndrewSvirin\Ebics\Handlers\UserSignatureHandlerV2;
use AndrewSvirin\Ebics\Models\Bank;
use AndrewSvirin\Ebics\Models\Http\Request;
use AndrewSvirin\Ebics\Models\Keyring;
use AndrewSvirin\Ebics\Models\UploadTransaction;
use AndrewSvirin\Ebics\Models\User;
use AndrewSvirin\Ebics\Models\UserSignature;
use AndrewSvirin\Ebics\Services\DigestResolverV2;
use DateTimeInterface;
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
        $this->orderDataHandler = new OrderDataHandlerV25($bank, $user, $keyring);
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

    protected function addOrderType(OrderDetailsBuilder $orderDetailsBuilder, string $orderType): OrderDetailsBuilder
    {
        switch ($orderType) {
            case 'INI':
            case 'HIA':
                $orderAttribute = OrderDetailsBuilder::ORDER_ATTRIBUTE_DZNNN;
                break;
            case 'CCT':
            case 'CDD':
            case 'XE2':
            case 'XE3':
            case 'CIP':
                $orderAttribute = $this->bank->usesUploadWithES() ?
                    OrderDetailsBuilder::ORDER_ATTRIBUTE_OZHNN : OrderDetailsBuilder::ORDER_ATTRIBUTE_DZHNN;
                break;
            case 'HVE':
                $orderAttribute = OrderDetailsBuilder::ORDER_ATTRIBUTE_UZHNN;
                break;
            default:
                $orderAttribute = OrderDetailsBuilder::ORDER_ATTRIBUTE_DZHNN;
        }

        return $orderDetailsBuilder
            ->addOrderType($orderType)
            ->addOrderAttribute($orderAttribute);
    }

    /**
     * @throws EbicsException
     */
    public function createVMK(
        DateTimeInterface $dateTime,
        DateTimeInterface $startDateTime = null,
        DateTimeInterface $endDateTime = null,
        int $segmentNumber = null,
        bool $isLastSegment = null
    ): Request {
        $context = (new RequestContext())
            ->setBank($this->bank)
            ->setUser($this->user)
            ->setKeyring($this->keyring)
            ->setDateTime($dateTime)
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
                                    ->addOrderType($orderDetailsBuilder, 'VMK')
                                    ->addStandardOrderParams($context->getStartDateTime(), $context->getEndDateTime());
                            })
                            ->addBankPubKeyDigests(
                                $context->getKeyring()->getBankSignatureXVersion(),
                                $this->digestResolver->digest($context->getKeyring()->getBankSignatureX()),
                                $context->getKeyring()->getBankSignatureEVersion(),
                                $this->digestResolver->digest($context->getKeyring()->getBankSignatureE())
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

    /**
     * @throws EbicsException
     */
    public function createSTA(
        DateTimeInterface $dateTime,
        DateTimeInterface $startDateTime = null,
        DateTimeInterface $endDateTime = null,
        int $segmentNumber = null,
        bool $isLastSegment = null
    ): Request {
        $context = (new RequestContext())
            ->setBank($this->bank)
            ->setUser($this->user)
            ->setKeyring($this->keyring)
            ->setDateTime($dateTime)
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
                                    ->addOrderType($orderDetailsBuilder, 'STA')
                                    ->addStandardOrderParams($context->getStartDateTime(), $context->getEndDateTime());
                            })
                            ->addBankPubKeyDigests(
                                $context->getKeyring()->getBankSignatureXVersion(),
                                $this->digestResolver->digest($context->getKeyring()->getBankSignatureX()),
                                $context->getKeyring()->getBankSignatureEVersion(),
                                $this->digestResolver->digest($context->getKeyring()->getBankSignatureE())
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

    /**
     * @throws EbicsException
     */
    public function createC52(
        DateTimeInterface $dateTime,
        DateTimeInterface $startDateTime = null,
        DateTimeInterface $endDateTime = null,
        int $segmentNumber = null,
        bool $isLastSegment = null
    ): Request {
        $context = (new RequestContext())
            ->setBank($this->bank)
            ->setUser($this->user)
            ->setKeyring($this->keyring)
            ->setDateTime($dateTime)
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
                                    ->addOrderType($orderDetailsBuilder, 'C52')
                                    ->addStandardOrderParams($context->getStartDateTime(), $context->getEndDateTime());
                            })
                            ->addBankPubKeyDigests(
                                $context->getKeyring()->getBankSignatureXVersion(),
                                $this->digestResolver->digest($context->getKeyring()->getBankSignatureX()),
                                $context->getKeyring()->getBankSignatureEVersion(),
                                $this->digestResolver->digest($context->getKeyring()->getBankSignatureE())
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

    /**
     * @throws EbicsException
     */
    public function createC53(
        DateTimeInterface $dateTime,
        DateTimeInterface $startDateTime = null,
        DateTimeInterface $endDateTime = null,
        int $segmentNumber = null,
        bool $isLastSegment = null
    ): Request {
        $context = (new RequestContext())
            ->setBank($this->bank)
            ->setUser($this->user)
            ->setKeyring($this->keyring)
            ->setDateTime($dateTime)
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
                                    ->addOrderType($orderDetailsBuilder, 'C53')
                                    ->addStandardOrderParams($context->getStartDateTime(), $context->getEndDateTime());
                            })
                            ->addBankPubKeyDigests(
                                $context->getKeyring()->getBankSignatureXVersion(),
                                $this->digestResolver->digest($context->getKeyring()->getBankSignatureX()),
                                $context->getKeyring()->getBankSignatureEVersion(),
                                $this->digestResolver->digest($context->getKeyring()->getBankSignatureE())
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

    /**
     * @throws EbicsException
     */
    public function createC54(
        DateTimeInterface $dateTime,
        DateTimeInterface $startDateTime = null,
        DateTimeInterface $endDateTime = null,
        int $segmentNumber = null,
        bool $isLastSegment = null
    ): Request {
        $context = (new RequestContext())
            ->setBank($this->bank)
            ->setUser($this->user)
            ->setKeyring($this->keyring)
            ->setDateTime($dateTime)
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
                                    ->addOrderType($orderDetailsBuilder, 'C54')
                                    ->addStandardOrderParams($context->getStartDateTime(), $context->getEndDateTime());
                            })
                            ->addBankPubKeyDigests(
                                $context->getKeyring()->getBankSignatureXVersion(),
                                $this->digestResolver->digest($context->getKeyring()->getBankSignatureX()),
                                $context->getKeyring()->getBankSignatureEVersion(),
                                $this->digestResolver->digest($context->getKeyring()->getBankSignatureE())
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

    /**
     * @throws EbicsException
     */
    public function createZ52(
        DateTimeInterface $dateTime,
        DateTimeInterface $startDateTime = null,
        DateTimeInterface $endDateTime = null,
        int $segmentNumber = null,
        bool $isLastSegment = null
    ): Request {
        $context = (new RequestContext())
            ->setBank($this->bank)
            ->setUser($this->user)
            ->setKeyring($this->keyring)
            ->setDateTime($dateTime)
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
                                    ->addOrderType($orderDetailsBuilder, 'Z52')
                                    ->addStandardOrderParams($context->getStartDateTime(), $context->getEndDateTime());
                            })
                            ->addBankPubKeyDigests(
                                $context->getKeyring()->getBankSignatureXVersion(),
                                $this->digestResolver->digest($context->getKeyring()->getBankSignatureX()),
                                $context->getKeyring()->getBankSignatureEVersion(),
                                $this->digestResolver->digest($context->getKeyring()->getBankSignatureE())
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

    /**
     * @throws EbicsException
     */
    public function createZ53(
        DateTimeInterface $dateTime,
        DateTimeInterface $startDateTime = null,
        DateTimeInterface $endDateTime = null,
        int $segmentNumber = null,
        bool $isLastSegment = null
    ): Request {
        $context = (new RequestContext())
            ->setBank($this->bank)
            ->setUser($this->user)
            ->setKeyring($this->keyring)
            ->setDateTime($dateTime)
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
                                    ->addOrderType($orderDetailsBuilder, 'Z53')
                                    ->addStandardOrderParams($context->getStartDateTime(), $context->getEndDateTime());
                            })
                            ->addBankPubKeyDigests(
                                $context->getKeyring()->getBankSignatureXVersion(),
                                $this->digestResolver->digest($context->getKeyring()->getBankSignatureX()),
                                $context->getKeyring()->getBankSignatureEVersion(),
                                $this->digestResolver->digest($context->getKeyring()->getBankSignatureE())
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
        $context = (new RequestContext())
            ->setBank($this->bank)
            ->setUser($this->user)
            ->setKeyring($this->keyring)
            ->setDateTime($dateTime)
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
                                    ->addOrderType($orderDetailsBuilder, 'Z54')
                                    ->addStandardOrderParams($context->getStartDateTime(), $context->getEndDateTime());
                            })
                            ->addBankPubKeyDigests(
                                $context->getKeyring()->getBankSignatureXVersion(),
                                $this->digestResolver->digest($context->getKeyring()->getBankSignatureX()),
                                $context->getKeyring()->getBankSignatureEVersion(),
                                $this->digestResolver->digest($context->getKeyring()->getBankSignatureE())
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

    public function createZSR(
        DateTimeInterface $dateTime,
        DateTimeInterface $startDateTime = null,
        DateTimeInterface $endDateTime = null,
        int $segmentNumber = null,
        bool $isLastSegment = null
    ): Request {
        throw new LogicException('Method not implemented yet for EBICS 2.5');
    }

    /**
     * @throws EbicsException
     */
    public function createCCT(DateTimeInterface $dateTime, UploadTransaction $transaction): Request
    {
        $signatureData = new UserSignature();
        $this->userSignatureHandler->handle($signatureData, $transaction->getDigest());

        $context = (new RequestContext())
            ->setBank($this->bank)
            ->setUser($this->user)
            ->setKeyring($this->keyring)
            ->setDateTime($dateTime)
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
                            ->addProduct('Ebics client PHP', 'de')
                            ->addOrderDetails(function (OrderDetailsBuilder $orderDetailsBuilder) {
                                $this
                                    ->addOrderType($orderDetailsBuilder, 'CCT')
                                    ->addStandardOrderParams();
                            })
                            ->addBankPubKeyDigests(
                                $context->getKeyring()->getBankSignatureXVersion(),
                                $this->digestResolver->digest($context->getKeyring()->getBankSignatureX()),
                                $context->getKeyring()->getBankSignatureEVersion(),
                                $this->digestResolver->digest($context->getKeyring()->getBankSignatureE())
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

    /**
     * @throws EbicsException
     */
    public function createCDD(DateTimeInterface $dateTime, UploadTransaction $transaction): Request
    {
        $signatureData = new UserSignature();
        $this->userSignatureHandler->handle($signatureData, $transaction->getDigest());

        $context = (new RequestContext())
            ->setBank($this->bank)
            ->setUser($this->user)
            ->setKeyring($this->keyring)
            ->setDateTime($dateTime)
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
                            ->addProduct('Ebics client PHP', 'de')
                            ->addOrderDetails(function (OrderDetailsBuilder $orderDetailsBuilder) {
                                $this
                                    ->addOrderType($orderDetailsBuilder, 'CDD')
                                    ->addStandardOrderParams();
                            })
                            ->addBankPubKeyDigests(
                                $context->getKeyring()->getBankSignatureXVersion(),
                                $this->digestResolver->digest($context->getKeyring()->getBankSignatureX()),
                                $context->getKeyring()->getBankSignatureEVersion(),
                                $this->digestResolver->digest($context->getKeyring()->getBankSignatureE())
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

    /**
     * @throws EbicsException
     */
    public function createXE2(DateTimeInterface $dateTime, UploadTransaction $transaction): Request
    {
        $signatureData = new UserSignature();
        $this->userSignatureHandler->handle($signatureData, $transaction->getDigest());

        $context = (new RequestContext())
            ->setBank($this->bank)
            ->setUser($this->user)
            ->setKeyring($this->keyring)
            ->setDateTime($dateTime)
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
                            ->addProduct('Ebics client PHP', 'de')
                            ->addOrderDetails(function (OrderDetailsBuilder $orderDetailsBuilder) {
                                $this
                                    ->addOrderType($orderDetailsBuilder, 'XE2')
                                    ->addStandardOrderParams();
                            })
                            ->addBankPubKeyDigests(
                                $context->getKeyring()->getBankSignatureXVersion(),
                                $this->digestResolver->digest($context->getKeyring()->getBankSignatureX()),
                                $context->getKeyring()->getBankSignatureEVersion(),
                                $this->digestResolver->digest($context->getKeyring()->getBankSignatureE())
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

    /**
     * @throws EbicsException
     */
    public function createXE3(DateTimeInterface $dateTime, UploadTransaction $transaction): Request
    {
        $signatureData = new UserSignature();
        $this->userSignatureHandler->handle($signatureData, $transaction->getDigest());

        $context = (new RequestContext())
            ->setBank($this->bank)
            ->setUser($this->user)
            ->setKeyring($this->keyring)
            ->setDateTime($dateTime)
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
                            ->addProduct('Ebics client PHP', 'de')
                            ->addOrderDetails(function (OrderDetailsBuilder $orderDetailsBuilder) {
                                $this
                                    ->addOrderType($orderDetailsBuilder, 'XE3')
                                    ->addStandardOrderParams();
                            })
                            ->addBankPubKeyDigests(
                                $context->getKeyring()->getBankSignatureXVersion(),
                                $this->digestResolver->digest($context->getKeyring()->getBankSignatureX()),
                                $context->getKeyring()->getBankSignatureEVersion(),
                                $this->digestResolver->digest($context->getKeyring()->getBankSignatureE())
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

    public function createYCT(DateTimeInterface $dateTime, UploadTransaction $transaction): Request
    {
        throw new LogicException('Method not implemented yet for EBICS 2.5');
    }
}
