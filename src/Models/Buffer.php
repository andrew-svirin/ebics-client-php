<?php

namespace EbicsApi\Ebics\Models;

use EbicsApi\Ebics\Contracts\BufferInterface;
use RuntimeException;

/**
 * Buffer class.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
final class Buffer implements BufferInterface
{
    /**
     * @var resource
     */
    private $stream;

    private int $length = 0;

    private string $filename;

    public function __construct(string $filename)
    {
        $this->filename = $filename;
    }

    public function __destruct()
    {
        $this->close();
    }

    public function open(string $mode): void
    {
        $stream = fopen($this->filename, $mode);

        if (false === $stream) {
            throw new RuntimeException('Unable to open stream');
        }

        $this->stream = $stream;
    }

    public function close(): void
    {
        fclose($this->stream);
    }

    public function rewind(): void
    {
        rewind($this->stream);
        $fstat = fstat($this->stream);

        if (false === $fstat) {
            throw new RuntimeException('Unable to fstat content');
        }

        $this->length = $fstat['size'];
    }

    public function write(string $string): void
    {
        fwrite($this->stream, $string);
        $this->length += strlen($string);
    }

    public function read(?int $length = null): string
    {
        $string = stream_get_contents($this->stream, $length ?? BufferInterface::DEFAULT_READ_LENGTH);

        if (false === $string) {
            throw new RuntimeException('Unable to read content');
        }

        $this->length -= strlen($string);

        return $string;
    }

    public function readContent(): string
    {
        $string = stream_get_contents($this->stream);

        if (false === $string) {
            throw new RuntimeException('Unable to read content');
        }

        $this->length -= strlen($string);

        return $string;
    }

    public function eof(): bool
    {
        return feof($this->stream);
    }

    public function fseek(int $offset): int
    {
        return fseek($this->stream, $offset);
    }

    public function filterAppend(string $filterName, int $mode): void
    {
        stream_filter_append($this->stream, $filterName, $mode);
    }

    public function length(): int
    {
        return $this->length;
    }
}
