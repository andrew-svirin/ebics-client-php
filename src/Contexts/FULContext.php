<?php

namespace AndrewSvirin\Ebics\Contexts;

final class FULContext
{
    private bool $test = false;
    private bool $ebcdic = false;

    public function isTest(): bool
    {
        return $this->test;
    }

    public function setTest(bool $test): FULContext
    {
        $this->test = $test;

        return $this;
    }

    public function isEbcdic(): bool
    {
        return $this->ebcdic;
    }

    public function setEbcdic(bool $ebcdic): FULContext
    {
        $this->ebcdic = $ebcdic;

        return $this;
    }
}
