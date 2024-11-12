<?php

namespace AndrewSvirin\Ebics\Factories;

use AndrewSvirin\Ebics\Contexts\RequestContext;
use AndrewSvirin\Ebics\Models\Http\Request;
use AndrewSvirin\Ebics\Models\UploadTransaction;
use LogicException;

/**
 * Ebics 2.x RequestFactory.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
abstract class RequestFactoryV2 extends RequestFactory
{
    public function createBTD(RequestContext $context): Request
    {
        throw new LogicException('Method for EBICS 3.0');
    }

    public function createBTU(UploadTransaction $transaction, RequestContext $context): Request
    {
        throw new LogicException('Method for EBICS 3.0');
    }
}
