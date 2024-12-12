<?php

namespace EbicsApi\Ebics\Handlers;

use EbicsApi\Ebics\Handlers\Traits\H004Trait;

/**
 * Ebics 2.5 AuthSignatureHandler.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 *
 * @internal
 */
final class AuthSignatureHandlerV25 extends AuthSignatureHandler
{
    use H004Trait;
}
