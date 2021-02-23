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

    /**
     * @param string $content
     *
     * @return OrderData
     */
    public function createOrderDataFromContent(string $content): OrderData
    {
        $orderData = new OrderData();
        $orderData->loadXML($content);

        return $orderData;
    }
}
