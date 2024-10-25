<?php

namespace AndrewSvirin\Ebics\Contracts;

use AndrewSvirin\Ebics\Models\TransferSegment;

/**
 * EBICS TransactionInterface representation.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
interface UploadTransactionInterface extends TransactionInterface
{
    public function getNumSegments(): int;

    /**
     * @return TransferSegment[]
     */
    public function getSegments(): array;
}
