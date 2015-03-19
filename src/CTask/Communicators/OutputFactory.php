<?php

namespace CTask\Communicators;

use Symfony\Component\Console\Output\OutputInterface;

interface OutputFactory
{
    /**
     * @return OutputInterface
     */
    public function getOutput();
}

?>