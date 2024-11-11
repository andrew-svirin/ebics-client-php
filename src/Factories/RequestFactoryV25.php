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
use DateTime;
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
    public function createVMK(
        ?DateTimeInterface $startDateTime,
        ?DateTimeInterface $endDateTime,
        bool $withES,
        ?DateTimeInterface $dateTime
    ): Request {
        $context = (new RequestContext())
            ->setBank($this->bank)
            ->setUser($this->user)
            ->setKeyring($this->keyring)
            ->setStartDateTime($startDateTime)
            ->setEndDateTime($endDateTime)
            ->setWithES($withES)
            ->setDateTime($dateTime ?? new DateTime());

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
                                    ->addOrderType($orderDetailsBuilder, 'VMK', $context->getWithES())
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
    public function createSTA(
        ?DateTimeInterface $startDateTime,
        ?DateTimeInterface $endDateTime,
        bool $withES,
        ?DateTimeInterface $dateTime
    ): Request {
        $context = (new RequestContext())
            ->setBank($this->bank)
            ->setUser($this->user)
            ->setKeyring($this->keyring)
            ->setStartDateTime($startDateTime)
            ->setEndDateTime($endDateTime)
            ->setWithES($withES)
            ->setDateTime($dateTime ?? new DateTime());

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
                                    ->addOrderType($orderDetailsBuilder, 'STA', $context->getWithES())
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
    public function createC52(
        ?DateTimeInterface $startDateTime,
        ?DateTimeInterface $endDateTime,
        bool $withES,
        ?DateTimeInterface $dateTime
    ): Request {
        $context = (new RequestContext())
            ->setBank($this->bank)
            ->setUser($this->user)
            ->setKeyring($this->keyring)
            ->setStartDateTime($startDateTime)
            ->setEndDateTime($endDateTime)
            ->setWithES($withES)
            ->setDateTime($dateTime ?? new DateTime());

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
                                    ->addOrderType($orderDetailsBuilder, 'C52', $context->getWithES())
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
    public function createC53(
        ?DateTimeInterface $startDateTime,
        ?DateTimeInterface $endDateTime,
        bool $withES,
        ?DateTimeInterface $dateTime
    ): Request {
        $context = (new RequestContext())
            ->setBank($this->bank)
            ->setUser($this->user)
            ->setKeyring($this->keyring)
            ->setStartDateTime($startDateTime)
            ->setEndDateTime($endDateTime)
            ->setWithES($withES)
            ->setDateTime($dateTime ?? new DateTime());

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
                                    ->addOrderType($orderDetailsBuilder, 'C53', $context->getWithES())
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
    public function createC54(
        ?DateTimeInterface $startDateTime,
        ?DateTimeInterface $endDateTime,
        bool $withES,
        ?DateTimeInterface $dateTime
    ): Request {
        $context = (new RequestContext())
            ->setBank($this->bank)
            ->setUser($this->user)
            ->setKeyring($this->keyring)
            ->setStartDateTime($startDateTime)
            ->setEndDateTime($endDateTime)
            ->setWithES($withES)
            ->setDateTime($dateTime ?? new DateTime());

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
                                    ->addOrderType($orderDetailsBuilder, 'C54', $context->getWithES())
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
    public function createZ52(
        ?DateTimeInterface $startDateTime,
        ?DateTimeInterface $endDateTime,
        bool $withES,
        ?DateTimeInterface $dateTime
    ): Request {
        $context = (new RequestContext())
            ->setBank($this->bank)
            ->setUser($this->user)
            ->setKeyring($this->keyring)
            ->setStartDateTime($startDateTime)
            ->setEndDateTime($endDateTime)
            ->setWithES($withES)
            ->setDateTime($dateTime ?? new DateTime());

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
                                    ->addOrderType($orderDetailsBuilder, 'Z52', $context->getWithES())
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
    public function createZ53(
        ?DateTimeInterface $startDateTime,
        ?DateTimeInterface $endDateTime,
        bool $withES,
        ?DateTimeInterface $dateTime
    ): Request {
        $context = (new RequestContext())
            ->setBank($this->bank)
            ->setUser($this->user)
            ->setKeyring($this->keyring)
            ->setStartDateTime($startDateTime)
            ->setEndDateTime($endDateTime)
            ->setWithES($withES)
            ->setDateTime($dateTime ?? new DateTime());

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
                                    ->addOrderType($orderDetailsBuilder, 'Z53', $context->getWithES())
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
    public function createZ54(
        ?DateTimeInterface $startDateTime,
        ?DateTimeInterface $endDateTime,
        bool $withES,
        ?DateTimeInterface $dateTime
    ): Request {
        $context = (new RequestContext())
            ->setBank($this->bank)
            ->setUser($this->user)
            ->setKeyring($this->keyring)
            ->setStartDateTime($startDateTime)
            ->setEndDateTime($endDateTime)
            ->setWithES($withES)
            ->setDateTime($dateTime ?? new DateTime());

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
                                    ->addOrderType($orderDetailsBuilder, 'Z54', $context->getWithES())
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

    public function createZSR(
        ?DateTimeInterface $startDateTime,
        ?DateTimeInterface $endDateTime,
        ?DateTimeInterface $dateTime
    ): Request {
        throw new LogicException('Method not implemented yet for EBICS 2.5');
    }

    public function createXEK(
        ?DateTimeInterface $startDateTime,
        ?DateTimeInterface $endDateTime,
        ?DateTimeInterface $dateTime
    ): Request {
        throw new LogicException('Method not implemented yet for EBICS 2.5');
    }

    /**
     * @throws EbicsException
     */
    public function createCCT(UploadTransaction $transaction, bool $withES, ?DateTimeInterface $dateTime): Request
    {
        $signatureData = new UserSignature();
        $this->userSignatureHandler->handle($signatureData, $transaction->getDigest());

        $context = (new RequestContext())
            ->setBank($this->bank)
            ->setUser($this->user)
            ->setKeyring($this->keyring)
            ->setWithES($withES)
            ->setDateTime($dateTime ?? new DateTime())
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
                            ->addOrderDetails(function (OrderDetailsBuilder $orderDetailsBuilder) use ($context) {
                                $this
                                    ->addOrderType($orderDetailsBuilder, 'CCT', $context->getWithES())
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

    /**
     * @throws EbicsException
     */
    public function createCDD(UploadTransaction $transaction, bool $withES, ?DateTimeInterface $dateTime): Request
    {
        $signatureData = new UserSignature();
        $this->userSignatureHandler->handle($signatureData, $transaction->getDigest());

        $context = (new RequestContext())
            ->setBank($this->bank)
            ->setUser($this->user)
            ->setKeyring($this->keyring)
            ->setWithES($withES)
            ->setDateTime($dateTime ?? new DateTime())
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
                            ->addOrderDetails(function (OrderDetailsBuilder $orderDetailsBuilder) use ($context) {
                                $this
                                    ->addOrderType($orderDetailsBuilder, 'CDD', $context->getWithES())
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

    /**
     * @throws EbicsException
     */
    public function createCDB(UploadTransaction $transaction, bool $withES, ?DateTimeInterface $dateTime): Request
    {
        $signatureData = new UserSignature();
        $this->userSignatureHandler->handle($signatureData, $transaction->getDigest());

        $context = (new RequestContext())
            ->setBank($this->bank)
            ->setUser($this->user)
            ->setKeyring($this->keyring)
            ->setWithES($withES)
            ->setDateTime($dateTime ?? new DateTime())
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
                            ->addOrderDetails(function (OrderDetailsBuilder $orderDetailsBuilder) use ($context) {
                                $this
                                    ->addOrderType($orderDetailsBuilder, 'CDB', $context->getWithES())
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

    /**
     * @throws EbicsException
     */
    public function createCIP(UploadTransaction $transaction, bool $withES, ?DateTimeInterface $dateTime): Request
    {
        $signatureData = new UserSignature();
        $this->userSignatureHandler->handle($signatureData, $transaction->getDigest());

        $context = (new RequestContext())
            ->setBank($this->bank)
            ->setUser($this->user)
            ->setKeyring($this->keyring)
            ->setWithES($withES)
            ->setDateTime($dateTime ?? new DateTime())
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
                            ->addOrderDetails(function (OrderDetailsBuilder $orderDetailsBuilder) use ($context) {
                                $this
                                    ->addOrderType($orderDetailsBuilder, 'CIP', $context->getWithES())
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

    /**
     * @throws EbicsException
     */
    public function createXE2(UploadTransaction $transaction, bool $withES, ?DateTimeInterface $dateTime): Request
    {
        $signatureData = new UserSignature();
        $this->userSignatureHandler->handle($signatureData, $transaction->getDigest());

        $context = (new RequestContext())
            ->setBank($this->bank)
            ->setUser($this->user)
            ->setKeyring($this->keyring)
            ->setWithES($withES)
            ->setDateTime($dateTime ?? new DateTime())
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
                            ->addOrderDetails(function (OrderDetailsBuilder $orderDetailsBuilder) use ($context) {
                                $this
                                    ->addOrderType($orderDetailsBuilder, 'XE2', $context->getWithES())
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

    /**
     * @throws EbicsException
     */
    public function createXE3(UploadTransaction $transaction, bool $withES, ?DateTimeInterface $dateTime): Request
    {
        $signatureData = new UserSignature();
        $this->userSignatureHandler->handle($signatureData, $transaction->getDigest());

        $context = (new RequestContext())
            ->setBank($this->bank)
            ->setUser($this->user)
            ->setKeyring($this->keyring)
            ->setWithES($withES)
            ->setDateTime($dateTime ?? new DateTime())
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
                            ->addOrderDetails(function (OrderDetailsBuilder $orderDetailsBuilder) use ($context) {
                                $this
                                    ->addOrderType($orderDetailsBuilder, 'XE3', $context->getWithES())
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

    public function createYCT(UploadTransaction $transaction, ?DateTimeInterface $dateTime): Request
    {
        throw new LogicException('Method not implemented yet for EBICS 2.5');
    }
}
