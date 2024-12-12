<?php

namespace EbicsApi\Ebics\Models;

/**
 * Ebics Order result.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
abstract class OrderResult
{
    private string $data;

    public function setData(string $data): void
    {
        $this->data = $data;
    }

    public function getData(): string
    {
        return $this->data;
    }
}
