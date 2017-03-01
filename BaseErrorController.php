<?php

namespace Piwi;


class BaseErrorController
{
    /** @var \Exception|\Throwable */
    protected $exception;

    public function __construct($ex)
    {
        $this->exception = $ex;
    }
}