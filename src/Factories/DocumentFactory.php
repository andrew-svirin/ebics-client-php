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

        $content = mb_convert_encoding($content, 'UTF-8', mb_list_encodings());

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
