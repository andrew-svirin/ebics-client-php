<?php

namespace EbicsApi\Ebics\Contracts;

/**
 * EBICS TransactionInterface representation.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
interface TransactionInterface
{
    public function getKey(): string;
}
