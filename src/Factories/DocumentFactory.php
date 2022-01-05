<?php

namespace AndrewSvirin\Ebics\Factories;

use AndrewSvirin\Ebics\Models\Document;

/**
 * Class DocumentFactory represents producers for the @see Document.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
final class DocumentFactory
{
    public function create(string $content): Document
    {
        $document = new Document();
        $document->loadXML($content);

        return $document;
    }
}
