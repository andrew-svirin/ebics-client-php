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
    /**
     * @param string $content requires already UTF-8 encoded content
     * @return Document
     */
    public function create(string $content): Document
    {
        $document = new Document();
        $document->loadXML($content);

        return $document;
    }

    /**
     * @param string[] $contents
     *
     * @return Document[]
     */
    public function createMultiple(array $contents): array
    {
        $documents = [];
        foreach ($contents as $key => $content) {
            $documents[$key] = $this->create($content);
        }

        return $documents;
    }
}
