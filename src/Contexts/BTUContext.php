<?php

namespace AndrewSvirin\Ebics\Contexts;

use AndrewSvirin\Ebics\Contracts\OrderDataInterface;

/**
 * Class BTUContext context container for BTU orders - requires EBICS 3.0
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
final class BTUContext extends BTFContext
{
    /**
     * @var string
     */
    private $fileData;

    /**
     * @var OrderDataInterface
     */
    private $orderData;

    public function setFileData(string $fileData): BTUContext
    {
        $this->fileData = $fileData;

        return $this;
    }

    public function getFileData(): string
    {
        return $this->fileData;
    }

    public function setFileDocument(OrderDataInterface $orderData): BTUContext
    {
        $this->orderData = $orderData;

        return $this;
    }

    public function getFileDocument(): OrderDataInterface
    {
        return $this->orderData;
    }
}
