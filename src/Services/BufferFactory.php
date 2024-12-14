<?php

namespace EbicsApi\Ebics\Services;

use EbicsApi\Ebics\Models\Buffer;

/**
 * Buffer factory.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 *
 * @internal
 */
final class BufferFactory
{
    private string $filename;

    public function __construct(string $filename)
    {
        $this->filename = $filename;
    }

    public function create(string $mode = 'w+'): Buffer
    {
        $new = new Buffer($this->filename);
        $new->open($mode);

        return $new;
    }

    public function createFromContent(string $content): Buffer
    {
        $new = $this->create();

        $new->write($content);
        $new->rewind();

        return $new;
    }
}
