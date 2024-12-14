<?php

namespace EbicsApi\Ebics\Contracts;

/**
 * Buffer class interface.
 *
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @author Andrew Svirin
 */
interface BufferInterface
{
    const DEFAULT_READ_LENGTH = 1024;

    /**
     * Open buffer.
     *
     * @param string $mode
     *
     * @return void
     */
    public function open(string $mode): void;

    /**
     * Close buffer.
     *
     * @return void
     */
    public function close(): void;

    /**
     * Reset pointer.
     *
     * @return void
     */
    public function rewind(): void;

    /**
     * Write to buffer.
     *
     * @param string $string
     *
     * @return void
     */
    public function write(string $string): void;

    /**
     * Read from buffer.
     *
     * @param int|null $length
     *
     * @return string
     */
    public function read(?int $length = null): string;

    /**
     * Read from buffer full content.
     *
     * @return string
     */
    public function readContent(): string;

    /**
     * Is end of file.
     *
     * @return bool
     */
    public function eof(): bool;

    /**
     * Move to pointer.
     *
     * @param int $offset
     *
     * @return int
     */
    public function fseek(int $offset): int;

    /**
     * Apply filter.
     *
     * @param string $filterName
     * @param int $mode
     *
     * @return void
     */
    public function filterAppend(string $filterName, int $mode): void;

    /**
     * Length of content.
     *
     * @return int
     */
    public function length(): int;
}
