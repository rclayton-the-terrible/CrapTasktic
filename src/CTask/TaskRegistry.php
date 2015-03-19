<?php

namespace CTask;

/**
 * Task Registry makes up for the fact that we don't have traits.  It's used
 * to register known implementations of Task so they can be retrieved by
 * the BaseCommands' __call() method.
 *
 * Interface TaskRegistry
 * @package CTask
 */
interface TaskRegistry
{
    /**
     * Register a task.
     * @param $taskClass string The class name (used to instantiate an instance of the task).
     * @param null $taskKey A more friendly name of the task.  This needs to be a valid PHP method name!!!!
     */
    public function register($taskClass, $taskKey = null);

    /**
     * Do we have an instance of the task class already?
     * @param $taskClass
     * @return boolean
     */
    public function contains($taskClass);

    /**
     * Has a task class already been registered for the supplied key?
     * @param $taskKey
     * @return boolean
     */
    public function containsKey($taskKey);

    /**
     * Create an new instance of the specified class.
     * @param $taskKey
     * @param $constructorArgs
     * @return mixed
     */
    public function newTaskInstance($taskKey, $constructorArgs);
}

?>