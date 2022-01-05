<?php

namespace AndrewSvirin\Ebics\Models;

use AndrewSvirin\Ebics\Models\Http\Response;

/**
 * Segment item.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
abstract class Segment
{
    /**
     * @var string
     */
    private $transactionKey;

    /**
     * @var Response
     */
    private $response;

    public function getTransactionKey(): string
    {
        return $this->transactionKey;
    }

    public function setTransactionKey(string $transactionKey): void
    {
        $this->transactionKey = $transactionKey;
    }

    public function setResponse(Response $response): void
    {
        $this->response = $response;
    }

    public function getResponse(): Response
    {
        return $this->response;
    }
}
