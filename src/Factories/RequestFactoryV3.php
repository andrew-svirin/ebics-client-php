<?php

namespace AndrewSvirin\Ebics\Factories;

use AndrewSvirin\Ebics\Builders\Request\OrderDetailsBuilder;
use AndrewSvirin\Ebics\Builders\Request\RequestBuilder;
use AndrewSvirin\Ebics\Builders\Request\XmlBuilderV3;
use AndrewSvirin\Ebics\Handlers\AuthSignatureHandlerV3;
use AndrewSvirin\Ebics\Handlers\OrderDataHandlerV3;
use AndrewSvirin\Ebics\Handlers\UserSignatureHandlerV3;
use AndrewSvirin\Ebics\Models\Bank;
use AndrewSvirin\Ebics\Models\Http\Request;
use AndrewSvirin\Ebics\Models\KeyRing;
use AndrewSvirin\Ebics\Models\User;
use AndrewSvirin\Ebics\Services\DigestResolverV3;

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
}
