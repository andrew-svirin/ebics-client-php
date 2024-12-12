<?php

namespace EbicsApi\Ebics\Handlers;

use EbicsApi\Ebics\Handlers\Traits\H003Trait;

/**
 * Ebics 2.4 AuthSignatureHandler.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 *
 * @internal
 */
final class AuthSignatureHandlerV24 extends AuthSignatureHandler
{
    use H003Trait;
}
