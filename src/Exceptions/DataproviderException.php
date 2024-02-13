<?php

namespace Lati111\LaravelDataproviders\Exceptions;

class DataproviderException extends \Exception
{
    public function __construct(string $message = '', int $code = 422) {
        parent::__construct($message, $code);
        $this->message = "$message";
        $this->code = $code;
    }
}