<?php

namespace EbicsApi\Ebics\Handlers;

use EbicsApi\Ebics\Handlers\Traits\H004Trait;

/**
 * Ebics 2.5 OrderDataHandler.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 *
 * @internal
 */
final class OrderDataHandlerV25 extends OrderDataHandlerV2
{
    use H004Trait;
}
