<?php

namespace AndrewSvirin\Ebics\Factories;

use AndrewSvirin\Ebics\Builders\Request\OrderDetailsBuilder;
use AndrewSvirin\Ebics\Builders\Request\RequestBuilder;
use AndrewSvirin\Ebics\Builders\Request\XmlBuilderV2;
use AndrewSvirin\Ebics\Handlers\AuthSignatureHandlerV2;
use AndrewSvirin\Ebics\Handlers\OrderDataHandlerV2;
use AndrewSvirin\Ebics\Handlers\UserSignatureHandlerV2;
use AndrewSvirin\Ebics\Models\Bank;
use AndrewSvirin\Ebics\Models\Http\Request;
use AndrewSvirin\Ebics\Models\KeyRing;
use AndrewSvirin\Ebics\Models\User;
use AndrewSvirin\Ebics\Services\DigestResolverV2;

/**
 * Ebics 2.5 RequestFactory.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
final class RequestFactoryV2 extends RequestFactory
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
        $this->authSignatureHandler = new AuthSignatureHandlerV2($keyRing);
        $this->userSignatureHandler = new UserSignatureHandlerV2($user, $keyRing);
        $this->orderDataHandler = new OrderDataHandlerV2($bank, $user, $keyRing);
        $this->digestResolver = new DigestResolverV2();
        parent::__construct($bank, $user, $keyRing);
    }

    protected function createRequestBuilderInstance(): RequestBuilder
    {
        return $this->requestBuilder
            ->createInstance(function (Request $request) {
                return new XmlBuilderV2($request);
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
            case 'CIP':
                $orderAttribute = OrderDetailsBuilder::ORDER_ATTRIBUTE_OZHNN;
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
}
