<?php

namespace Piwi;


class BaseErrorController
{
    protected $exception;

    public function __construct($ex)
    {
        $this->exception = $ex;
    }
}