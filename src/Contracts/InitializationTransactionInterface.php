<?php

namespace EbicsApi\Ebics\Contracts;

use EbicsApi\Ebics\Models\InitializationSegment;

/**
 * EBICS InitializationTransactionInterface representation.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
interface InitializationTransactionInterface extends TransactionInterface
{
    public function getOrderData(): string;

    public function getInitializationSegment(): InitializationSegment;
}
