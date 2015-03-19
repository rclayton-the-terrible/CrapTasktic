<?php

namespace CTask;

/**
 * A unit of work in the Task Runner.
 *
 * Interface Task
 * @package CTask
 */
interface Task
{
    /**
     * Initialize the task with the essential services for performing tasks.
     * @param Configuration $configuration configuration for the application instance.
     * @param Communicator $communicator mechanism for communicating to the client.
     */
    public function init(Configuration $configuration, Communicator $communicator);

    /**
     * Execute the task and return the result.  The result must be of type CTask\Result;
     * if it is not, you are a very bad person.
     * @return Result
     */
    public function execute();
}

?>