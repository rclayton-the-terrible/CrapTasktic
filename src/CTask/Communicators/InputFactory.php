<?php

namespace CTask\Communicators;

use Symfony\Component\Console\Input\InputInterface;

interface InputFactory
{
    /**
     * @param $input string input (if present).
     * @return InputInterface
     */
    public function getInput($input = null);
}