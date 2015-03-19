<?php

namespace CTask\Tasks;

use CTask\Communicator;
use CTask\Configuration;
use CTask\Task;

abstract class BaseTask implements Task
{
    /**
     * @var Configuration
     */
    protected $configuration;

    /**
     * @var Communicator
     */
    protected $communicator;

    /**
     * Initialize the task with the essential services for performing tasks.
     * @param Configuration $configuration configuration for the application instance.
     * @param Communicator $communicator mechanism for communicating to the client.
     */
    public function init(Configuration $configuration, Communicator $communicator)
    {
        $this->configuration = $configuration;
        $this->communicator = $communicator;
    }

    protected function printTaskInfo($text, $task = null)
    {
        $name = $this->getPrintedTaskName($task);
        $this->communicator->write(" <fg=white;bg=cyan;options=bold>[$name]</fg=white;bg=cyan;options=bold> $text");
    }
    protected function printTaskSuccess($text, $task = null)
    {
        $name = $this->getPrintedTaskName($task);
        $this->communicator->write(" <fg=white;bg=green;options=bold>[$name]</fg=white;bg=green;options=bold> $text");
    }
    protected function printTaskError($text, $task = null)
    {
        $name = $this->getPrintedTaskName($task);
        $this->communicator->write(" <fg=white;bg=red;options=bold>[$name]</fg=white;bg=red;options=bold> $text");
    }
    protected function getPrintedTaskName($task = null)
    {
        if (!$task) {
            $task = $this;
        }
        $name = get_class($task);
        $name = preg_replace('~Stack^~', '' , $name);
        $name = str_replace('CTask\Task\FileSystem\\', '' , $name);
        $name = str_replace('CTask\Task\\', '' , $name);
        return $name;
    }
}

?>