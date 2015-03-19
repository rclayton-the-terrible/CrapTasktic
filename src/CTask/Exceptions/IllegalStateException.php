<?php

namespace CTask\Exceptions;


class IllegalStateException extends \RuntimeException
{
    public function __construct($message = 'The state of a parameter or condition is invalid.')
    {
        parent::__construct($message);
    }
}