<?php

namespace AndrewSvirin\Ebics\Factories;

use AndrewSvirin\Ebics\Models\OrderData;

/**
 * Class OrderDataFactory represents producers for the @see OrderData.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
class OrderDataFactory
{
    public static function buildOrderDataFromContent(string $content): OrderData
    {
        $orderData = new OrderData();
        $orderData->loadXML($content);

        return $orderData;
    }
}
