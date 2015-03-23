<?php

namespace CTask\Tasks;

use CTask\Preconditions;
use CTask\TaskRegistry;

/**
 * Default Task Registry.  Uses static properties to keep track of tasks.  Just new up an instance
 * to add more.
 *
 * Class DefaultTaskRegistry
 * @package CTask\Tasks
 */
class DefaultTaskRegistry implements TaskRegistry
{
    public static $tasks = array(
        'CTask\Tasks\Base\Exec',
        'CTask\Tasks\Base\ParallelExec',
        'CTask\Tasks\Base\Stack',
        'CTask\Tasks\Base\Watch',
        'CTask\Tasks\FileSystem\CopyDir',
        'CTask\Tasks\FileSystem\CleanDir',
        'CTask\Tasks\FileSystem\DeleteDir',
        'CTask\Tasks\FileSystem\MakeDir',
        'CTask\Tasks\FileSystem\MirrorDir',
        'CTask\Tasks\File\Concat',
        'CTask\Tasks\File\Replace',
        'CTask\Tasks\File\Write',
        'CTask\Tasks\File\Delete',
    );

    public static $keyToTaskMapping = array(
        'exec' => 'CTask\Tasks\Base\Exec',
        'pexec' => 'CTask\Tasks\Base\ParallelExec',
        'stack' => 'CTask\Tasks\Base\Stack',
        'watch' => 'CTask\Tasks\Base\Watch',
        'copyDir' => 'CTask\Tasks\FileSystem\CopyDir',
        'cleanDir' => 'CTask\Tasks\FileSystem\CleanDir',
        'deleteDir' => 'CTask\Tasks\FileSystem\DeleteDir',
        'mirrorDir' => 'CTask\Tasks\FileSystem\MirrorDir',
        'mkdir' => 'CTask\Tasks\FileSystem\MakeDir',
        'concat' => 'CTask\Tasks\File\Concat',
        'replace' => 'CTask\Tasks\File\Replace',
        'write' => 'CTask\Tasks\File\Write',
        'rm' => 'CTask\Tasks\File\Delete',
    );

    /**
     * Register a task.
     * @param $taskClass string The class name (used to instantiate an instance of the task).
     * @param null $taskKey A more friendly name of the task.  This needs to be a valid PHP method name!!!!
     */
    public function register($taskClass, $taskKey = null)
    {
        Preconditions::assertClassExists($taskClass);

        if (!$this->contains($taskClass)) self::$tasks[] = $taskClass;

        if ($taskKey !== null)
        {
            if (!$this->containsKey($taskKey)) self::$keyToTaskMapping[$taskKey] = $taskClass;
        }
        else
        {
            if (!$this->containsKey($taskClass)) self::$keyToTaskMapping[$taskClass] = $taskClass;
        }
    }

    /**
     * Do we have an instance of the task class already?
     * @param $taskClass
     * @return boolean
     */
    public function contains($taskClass)
    {
        return array_key_exists($taskClass, self::$tasks);
    }

    /**
     * Has a task class already been registered for the supplied key?
     * @param $taskKey
     * @return boolean
     */
    public function containsKey($taskKey)
    {
        return array_key_exists($taskKey, self::$keyToTaskMapping);
    }

    /**
     * Create an new instance of the specified class.
     * @param $taskKey
     * @param $constructorArgs
     * @return mixed
     */
    public function newTaskInstance($taskKey, $constructorArgs)
    {
        Preconditions::assertArrayContainsKey(self::$keyToTaskMapping, $taskKey);

        $taskClass = self::$keyToTaskMapping[$taskKey];

        $refClass = new \ReflectionClass($taskClass);

        Preconditions::assert($refClass->implementsInterface('CTask\Task'));

        return $refClass->newInstanceArgs($constructorArgs);
    }
}

?>