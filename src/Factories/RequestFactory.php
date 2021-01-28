<?php

namespace AndrewSvirin\Ebics\Factories;

use AndrewSvirin\Ebics\Builders\BodyBuilder;
use AndrewSvirin\Ebics\Builders\DataTransferBuilder;
use AndrewSvirin\Ebics\Builders\HeaderBuilder;
use AndrewSvirin\Ebics\Builders\MutableBuilder;
use AndrewSvirin\Ebics\Builders\OrderDetailsBuilder;
use AndrewSvirin\Ebics\Builders\RequestBuilder;
use AndrewSvirin\Ebics\Builders\StaticBuilder;
use AndrewSvirin\Ebics\Builders\TransferReceiptBuilder;
use AndrewSvirin\Ebics\Builders\XmlBuilder;
use AndrewSvirin\Ebics\Contexts\RequestContext;
use AndrewSvirin\Ebics\Contracts\SignatureInterface;
use AndrewSvirin\Ebics\Exceptions\EbicsException;
use AndrewSvirin\Ebics\Handlers\AuthSignatureHandler;
use AndrewSvirin\Ebics\Handlers\OrderDataHandler;
use AndrewSvirin\Ebics\Models\Bank;
use AndrewSvirin\Ebics\Models\Http\Request;
use AndrewSvirin\Ebics\Models\KeyRing;
use AndrewSvirin\Ebics\Models\OrderData;
use AndrewSvirin\Ebics\Models\User;
use DateTime;

/**
 * Class RequestFactory represents producers for the @see Request.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
class RequestFactory
{
    /**
     * @var RequestBuilder
     */
    private $requestBuilder;

    /**
     * @var OrderDataHandler
     */
    private $orderDataHandler;

    /**
     * @var AuthSignatureHandler
     */
    private $authSignatureHandler;

    /**
     * @var Bank
     */
    private $bank;

    /**
     * @var User
     */
    private $user;

    /**
     * @var KeyRing
     */
    private $keyRing;

    /**
     * Constructor.
     *
     * @param Bank $bank
     * @param User $user
     * @param KeyRing $keyRing
     */
    public function __construct(Bank $bank, User $user, KeyRing $keyRing)
    {
        $this->requestBuilder = new RequestBuilder();
        $this->orderDataHandler = new OrderDataHandler($bank, $user, $keyRing);
        $this->authSignatureHandler = new AuthSignatureHandler($keyRing);

        $this->bank = $bank;
        $this->user = $user;
        $this->keyRing = $keyRing;
    }

    public function createINI(SignatureInterface $certificateA, DateTime $dateTime): Request
    {
        $context = (new RequestContext())
            ->setBank($this->bank)
            ->setUser($this->user)
            ->setCertificateA($certificateA)
            ->setDateTime($dateTime);

        $request = $this->requestBuilder
            ->createInstance()
            ->addContainerUnsecured(function (XmlBuilder $builder) use ($context) {
                $builder->addHeader(function (HeaderBuilder $builder) use ($context) {
                    $builder->addStatic(function (StaticBuilder $builder) use ($context) {
                        $builder
                            ->addHostId($context->getBank()->getHostId())
                            ->addPartnerId($context->getUser()->getPartnerId())
                            ->addUserId($context->getUser()->getUserId())
                            ->addProduct('Ebics client PHP', 'de')
                            ->addOrderDetails(function (OrderDetailsBuilder $orderDetailsBuilder) {
                                $orderDetailsBuilder
                                    ->addOrderType('INI')
                                    ->addOrderAttribute(OrderDetailsBuilder::ORDER_ATTRIBUTE_DZNNN);
                            })
                            ->addSecurityMedium(StaticBuilder::SECURITY_MEDIUM_0000);
                    })->addMutable();
                })->addBody(function (BodyBuilder $builder) use ($context) {
                    $builder->addDataTransfer(function (DataTransferBuilder $builder) use ($context) {
                        $orderData = new OrderData();
                        $this->orderDataHandler->handleINI(
                            $orderData,
                            $context->getCertificateA(),
                            $context->getDateTime()
                        );
                        $builder->addOrderData($orderData->getContent());
                    });
                });
            })
            ->popInstance();

        return $request;
    }

    public function createHEV(): Request
    {
        $context = (new RequestContext())
            ->setBank($this->bank);

        $request = $this->requestBuilder->createInstance()
            ->addContainerHEV(function (XmlBuilder $builder) use ($context) {
                $builder->addHostId($context->getBank()->getHostId());
            })
            ->popInstance();

        return $request;
    }

    public function createHIA(
        SignatureInterface $certificateE,
        SignatureInterface $certificateX,
        DateTime $dateTime
    ): Request {
        $context = (new RequestContext())
            ->setBank($this->bank)
            ->setUser($this->user)
            ->setCertificateE($certificateE)
            ->setCertificateX($certificateX)
            ->setDateTime($dateTime);

        $request = $this->requestBuilder
            ->createInstance()
            ->addContainerUnsecured(function (XmlBuilder $builder) use ($context) {
                $builder->addHeader(function (HeaderBuilder $builder) use ($context) {
                    $builder->addStatic(function (StaticBuilder $builder) use ($context) {
                        $builder
                            ->addHostId($context->getBank()->getHostId())
                            ->addPartnerId($context->getUser()->getPartnerId())
                            ->addUserId($context->getUser()->getUserId())
                            ->addProduct('Ebics client PHP', 'de')
                            ->addOrderDetails(function (OrderDetailsBuilder $orderDetailsBuilder) {
                                $orderDetailsBuilder
                                    ->addOrderType('HIA')
                                    ->addOrderAttribute(OrderDetailsBuilder::ORDER_ATTRIBUTE_DZNNN);
                            })
                            ->addSecurityMedium(StaticBuilder::SECURITY_MEDIUM_0000);
                    })->addMutable();
                })->addBody(function (BodyBuilder $builder) use ($context) {
                    $builder->addDataTransfer(function (DataTransferBuilder $builder) use ($context) {
                        $orderData = new OrderData();
                        $this->orderDataHandler->handleHIA(
                            $orderData,
                            $context->getCertificateE(),
                            $context->getCertificateX(),
                            $context->getDateTime()
                        );
                        $builder->addOrderData($orderData->getContent());
                    });
                });
            })
            ->popInstance();

        return $request;
    }

    /**
     * @param DateTime $dateTime
     *
     * @return Request
     * @throws EbicsException
     */
    public function createHPB(DateTime $dateTime): Request
    {
        $context = (new RequestContext())
            ->setBank($this->bank)
            ->setUser($this->user)
            ->setDateTime($dateTime);

        $request = $this->requestBuilder
            ->createInstance()
            ->addContainerSecuredNoPubKeyDigests(function (XmlBuilder $builder) use ($context) {
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
                                $orderDetailsBuilder
                                    ->addOrderType('HPB')
                                    ->addOrderAttribute(OrderDetailsBuilder::ORDER_ATTRIBUTE_DZHNN);
                            })
                            ->addSecurityMedium(StaticBuilder::SECURITY_MEDIUM_0000);
                    })->addMutable();
                })->addBody();
            })
            ->popInstance();

        $this->authSignatureHandler->handle($request);

        return $request;
    }

    /**
     * @param DateTime $dateTime
     *
     * @return Request
     * @throws EbicsException
     */
    public function createHPD(DateTime $dateTime): Request
    {
        $context = (new RequestContext())
            ->setBank($this->bank)
            ->setUser($this->user)
            ->setKeyRing($this->keyRing)
            ->setDateTime($dateTime);

        $request = $this->requestBuilder
            ->createInstance()
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
                                $orderDetailsBuilder
                                    ->addOrderType('HPD')
                                    ->addOrderAttribute(OrderDetailsBuilder::ORDER_ATTRIBUTE_DZHNN)
                                    ->addStandardOrderParams();
                            })
                            ->addBank($context->getKeyRing())
                            ->addSecurityMedium(StaticBuilder::SECURITY_MEDIUM_0000);
                    })->addMutable(function (MutableBuilder $builder) {
                        $builder->addTransactionPhase(MutableBuilder::PHASE_INITIALIZATION);
                    });
                })->addBody();
            })
            ->popInstance();

        $this->authSignatureHandler->handle($request);

        return $request;
    }

    /**
     * @param DateTime $dateTime
     *
     * @return Request
     * @throws EbicsException
     */
    public function createHKD(DateTime $dateTime): Request
    {
        $context = (new RequestContext())
            ->setBank($this->bank)
            ->setUser($this->user)
            ->setKeyRing($this->keyRing)
            ->setDateTime($dateTime);

        $request = $this->requestBuilder
            ->createInstance()
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
                                $orderDetailsBuilder
                                    ->addOrderType('HKD')
                                    ->addOrderAttribute(OrderDetailsBuilder::ORDER_ATTRIBUTE_DZHNN)
                                    ->addStandardOrderParams();
                            })
                            ->addBank($context->getKeyRing())
                            ->addSecurityMedium(StaticBuilder::SECURITY_MEDIUM_0000);
                    })->addMutable(function (MutableBuilder $builder) {
                        $builder->addTransactionPhase(MutableBuilder::PHASE_INITIALIZATION);
                    });
                })->addBody();
            })
            ->popInstance();

        $this->authSignatureHandler->handle($request);

        return $request;
    }

    /**
     * @param DateTime $dateTime
     *
     * @return Request
     * @throws EbicsException
     */
    public function createHTD(DateTime $dateTime): Request
    {
        $context = (new RequestContext())
            ->setBank($this->bank)
            ->setUser($this->user)
            ->setKeyRing($this->keyRing)
            ->setDateTime($dateTime);

        $request = $this->requestBuilder
            ->createInstance()
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
                                $orderDetailsBuilder
                                    ->addOrderType('HTD')
                                    ->addOrderAttribute(OrderDetailsBuilder::ORDER_ATTRIBUTE_DZHNN)
                                    ->addStandardOrderParams();
                            })
                            ->addBank($context->getKeyRing())
                            ->addSecurityMedium(StaticBuilder::SECURITY_MEDIUM_0000);
                    })->addMutable(function (MutableBuilder $builder) {
                        $builder->addTransactionPhase(MutableBuilder::PHASE_INITIALIZATION);
                    });
                })->addBody();
            })
            ->popInstance();

        $this->authSignatureHandler->handle($request);

        return $request;
    }

    /**
     * @param DateTime $dateTime
     * @param string $fileFormat
     * @param string $countryCode
     * @param DateTime|null $startDateTime
     * @param DateTime|null $endDateTime
     *
     * @return Request
     * @throws EbicsException
     */
    public function createFDL(
        DateTime $dateTime,
        string $fileFormat,
        string $countryCode = 'FR',
        DateTime $startDateTime = null,
        DateTime $endDateTime = null
    ): Request {
        $context = (new RequestContext())
            ->setBank($this->bank)
            ->setUser($this->user)
            ->setKeyRing($this->keyRing)
            ->setDateTime($dateTime)
            ->setFileFormat($fileFormat)
            ->setCountryCode($countryCode)
            ->setStartDateTime($startDateTime)
            ->setEndDateTime($endDateTime);

        $request = $this->requestBuilder
            ->createInstance()
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
                                $orderDetailsBuilder
                                    ->addOrderType('FDL')
                                    ->addOrderAttribute(OrderDetailsBuilder::ORDER_ATTRIBUTE_DZHNN)
                                    ->addFDLOrderParams(
                                        $context->getFileFormat(),
                                        $context->getCountryCode(),
                                        $context->getStartDateTime(),
                                        $context->getEndDateTime()
                                    );
                            })
                            ->addBank($context->getKeyRing())
                            ->addSecurityMedium(StaticBuilder::SECURITY_MEDIUM_0000);
                    })->addMutable(function (MutableBuilder $builder) {
                        $builder->addTransactionPhase(MutableBuilder::PHASE_INITIALIZATION);
                    });
                })->addBody();
            })
            ->popInstance();

        $this->authSignatureHandler->handle($request);

        return $request;
    }

    /**
     * @param DateTime $dateTime
     *
     * @return Request
     * @throws EbicsException
     */
    public function createHAA(DateTime $dateTime): Request
    {
        $context = (new RequestContext())
            ->setBank($this->bank)
            ->setUser($this->user)
            ->setKeyRing($this->keyRing)
            ->setDateTime($dateTime);

        $request = $this->requestBuilder
            ->createInstance()
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
                                $orderDetailsBuilder
                                    ->addOrderType('HAA')
                                    ->addOrderAttribute(OrderDetailsBuilder::ORDER_ATTRIBUTE_DZHNN)
                                    ->addStandardOrderParams();
                            })
                            ->addBank($context->getKeyRing())
                            ->addSecurityMedium(StaticBuilder::SECURITY_MEDIUM_0000);
                    })->addMutable(function (MutableBuilder $builder) {
                        $builder->addTransactionPhase(MutableBuilder::PHASE_INITIALIZATION);
                    });
                })->addBody();
            })
            ->popInstance();

        $this->authSignatureHandler->handle($request);

        return $request;
    }

    /**
     * @param string $transactionId
     * @param bool $acknowledged
     *
     * @return Request
     * @throws EbicsException
     */
    public function createTransferReceipt(string $transactionId, bool $acknowledged): Request
    {
        $context = (new RequestContext())
            ->setBank($this->bank)
            ->setTransactionId($transactionId)
            ->setReceiptCode(true === $acknowledged ?
                TransferReceiptBuilder::CODE_RECEIPT_POSITIVE : TransferReceiptBuilder::CODE_RECEIPT_NEGATIVE);

        $request = $this->requestBuilder
            ->createInstance()
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
     * @param DateTime $dateTime
     * @param DateTime|null $startDateTime
     * @param DateTime|null $endDateTime
     *
     * @return Request
     * @throws EbicsException
     */
    public function createVMK(DateTime $dateTime, DateTime $startDateTime = null, DateTime $endDateTime = null): Request
    {
        $context = (new RequestContext())
            ->setBank($this->bank)
            ->setUser($this->user)
            ->setKeyRing($this->keyRing)
            ->setDateTime($dateTime)
            ->setStartDateTime($startDateTime)
            ->setEndDateTime($endDateTime);

        $request = $this->requestBuilder
            ->createInstance()
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
                                $orderDetailsBuilder
                                    ->addOrderType('VMK')
                                    ->addOrderAttribute(OrderDetailsBuilder::ORDER_ATTRIBUTE_DZHNN)
                                    ->addStandardOrderParams($context->getStartDateTime(), $context->getEndDateTime());
                            })
                            ->addBank($context->getKeyRing())
                            ->addSecurityMedium(StaticBuilder::SECURITY_MEDIUM_0000);
                    })->addMutable(function (MutableBuilder $builder) {
                        $builder->addTransactionPhase(MutableBuilder::PHASE_INITIALIZATION);
                    });
                })->addBody();
            })
            ->popInstance();

        $this->authSignatureHandler->handle($request);

        return $request;
    }

    /**
     * @param DateTime $dateTime
     * @param DateTime|null $startDateTime
     * @param DateTime|null $endDateTime
     *
     * @return Request
     * @throws EbicsException
     */
    public function createSTA(DateTime $dateTime, DateTime $startDateTime = null, DateTime $endDateTime = null): Request
    {
        $context = (new RequestContext())
            ->setBank($this->bank)
            ->setUser($this->user)
            ->setKeyRing($this->keyRing)
            ->setDateTime($dateTime)
            ->setStartDateTime($startDateTime)
            ->setEndDateTime($endDateTime);

        $request = $this->requestBuilder
            ->createInstance()
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
                                $orderDetailsBuilder
                                    ->addOrderType('STA')
                                    ->addOrderAttribute(OrderDetailsBuilder::ORDER_ATTRIBUTE_DZHNN)
                                    ->addStandardOrderParams($context->getStartDateTime(), $context->getEndDateTime());
                            })
                            ->addBank($context->getKeyRing())
                            ->addSecurityMedium(StaticBuilder::SECURITY_MEDIUM_0000);
                    })->addMutable(function (MutableBuilder $builder) {
                        $builder->addTransactionPhase(MutableBuilder::PHASE_INITIALIZATION);
                    });
                })->addBody();
            })
            ->popInstance();

        $this->authSignatureHandler->handle($request);

        return $request;
    }

    /**
     * @param DateTime $dateTime
     * @param DateTime|null $startDateTime
     * @param DateTime|null $endDateTime
     *
     * @return Request
     * @throws EbicsException
     */
    public function createC53(DateTime $dateTime, DateTime $startDateTime = null, DateTime $endDateTime = null): Request
    {
        $context = (new RequestContext())
            ->setBank($this->bank)
            ->setUser($this->user)
            ->setKeyRing($this->keyRing)
            ->setDateTime($dateTime)
            ->setStartDateTime($startDateTime)
            ->setEndDateTime($endDateTime);

        $request = $this->requestBuilder
            ->createInstance()
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
                                $orderDetailsBuilder
                                    ->addOrderType('C53')
                                    ->addOrderAttribute(OrderDetailsBuilder::ORDER_ATTRIBUTE_DZHNN)
                                    ->addStandardOrderParams($context->getStartDateTime(), $context->getEndDateTime());
                            })
                            ->addBank($context->getKeyRing())
                            ->addSecurityMedium(StaticBuilder::SECURITY_MEDIUM_0000);
                    })->addMutable(function (MutableBuilder $builder) {
                        $builder->addTransactionPhase(MutableBuilder::PHASE_INITIALIZATION);
                    });
                })->addBody();
            })
            ->popInstance();

        $this->authSignatureHandler->handle($request);

        return $request;
    }

    /**
     * @param DateTime $dateTime
     * @param DateTime|null $startDateTime
     * @param DateTime|null $endDateTime
     *
     * @return Request
     * @throws EbicsException
     */
    public function createZ53(DateTime $dateTime, DateTime $startDateTime = null, DateTime $endDateTime = null): Request
    {
        $context = (new RequestContext())
            ->setBank($this->bank)
            ->setUser($this->user)
            ->setKeyRing($this->keyRing)
            ->setDateTime($dateTime)
            ->setStartDateTime($startDateTime)
            ->setEndDateTime($endDateTime);

        $request = $this->requestBuilder
            ->createInstance()
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
                                $orderDetailsBuilder
                                    ->addOrderType('Z53')
                                    ->addOrderAttribute(OrderDetailsBuilder::ORDER_ATTRIBUTE_DZHNN)
                                    ->addStandardOrderParams($context->getStartDateTime(), $context->getEndDateTime());
                            })
                            ->addBank($context->getKeyRing())
                            ->addSecurityMedium(StaticBuilder::SECURITY_MEDIUM_0000);
                    })->addMutable(function (MutableBuilder $builder) {
                        $builder->addTransactionPhase(MutableBuilder::PHASE_INITIALIZATION);
                    });
                })->addBody();
            })
            ->popInstance();

        $this->authSignatureHandler->handle($request);

        return $request;
    }
}
