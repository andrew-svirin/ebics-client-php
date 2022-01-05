<?php

namespace AndrewSvirin\Ebics\Contracts;

use AndrewSvirin\Ebics\Models\Http\Response;

/**
 * EBICS TransactionInterface representation.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
interface DownloadTransactionInterface extends TransactionInterface
{
    public function getId(): ?string;

    public function getReceipt(): Response;

    public function getNumSegments(): int;
}
