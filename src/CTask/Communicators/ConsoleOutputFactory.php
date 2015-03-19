<?php

namespace CTask\Communicators;

use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

class ConsoleOutputFactory implements OutputFactory
{
    private $output;

    public function __construct()
    {
        $this->output = new ConsoleOutput();
    }

    /**
     * @return OutputInterface
     */
    public function getOutput()
    {
        return $this->output;
    }
}