<?php

namespace CTask\Communicators;


use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputInterface;

class ArgvInputFactory implements InputFactory
{
    /**
     * @param $input string console input.
     * @return InputInterface
     */
    public function getInput($input = null)
    {
        return new ArgvInput($input);
    }
}

?>