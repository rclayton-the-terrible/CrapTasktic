<?php

namespace CTask\Tasks;


use CTask\Communicator;
use CTask\Configuration;
use CTask\Preconditions;
use CTask\Task;
use CTask\Tasks\Archive\CreateTarball;
use CTask\Tasks\Base\Exec;
use CTask\Tasks\Base\ParallelExec;
use CTask\Tasks\Base\Stack;
use CTask\Tasks\Base\Watch;
use CTask\Tasks\File\Concat;
use CTask\Tasks\File\Delete;
use CTask\Tasks\File\Replace;
use CTask\Tasks\File\Write;
use CTask\Tasks\FileSystem\CleanDir;
use CTask\Tasks\FileSystem\CopyDir;
use CTask\Tasks\FileSystem\DeleteDir;
use CTask\Tasks\FileSystem\MakeDir;
use CTask\Tasks\FileSystem\MirrorDir;

/**
 * This is simply a registry of tasks that can be created in our DSL.
 *
 * Class Tasks
 * @package CTask\Tasks
 */
class Tasks
{
    /**
     * @var Communicator
     */
    private $communicator;

    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * This is available so it can be overridden for testing.
     * @var array
     */
    public $mapping = array(
        'exec' => 'CTask\Tasks\Base\Exec',
        'pexec' => 'CTask\Tasks\Base\ParallelExec',
        'stack' => 'CTask\Tasks\Base\Stack',
        'watch' => 'CTask\Tasks\Base\Watch',
        'concat' => 'CTask\Tasks\File\Concat',
        'delete' => 'CTask\Tasks\File\Delete',
        'replace' => 'CTask\Tasks\File\Replace',
        'write' => 'CTask\Tasks\File\Write',
        'cleanDir' => 'CTask\Tasks\FileSystem\CleanDir',
        'copyDir' => 'CTask\Tasks\FileSystem\CopyDir',
        'deleteDir' => 'CTask\Tasks\FileSystem\DeleteDir',
        'makeDir' => 'CTask\Tasks\FileSystem\MakeDir',
        'mirrorDir' => 'CTask\Tasks\FileSystem\MirrorDir',
        'createTarball' => 'CTask\Tasks\Archive\CreateTarball',
    );

    /**
     * Instantiate with a reference to the configuration and communicator.
     * @param $configuration
     * @param $communicator
     */
    function __construct(Configuration $configuration, Communicator $communicator)
    {
        $this->communicator = $communicator;
        $this->configuration = $configuration;
    }

    /**
     * Execute a Cli Command.
     * @param $cliCommand
     * @return Exec
     */
    public function exec($cliCommand)
    {
        return $this->createInstance('exec', array($cliCommand));
    }

    /**
     * Execute a number of Cli Commands in parallel.
     * @return ParallelExec
     */
    public function pexec()
    {
        return $this->createInstance('pexec');
    }

    /**
     * Execute a series of tasks sequentially, optionally breaking on error.
     * @param array $tasks
     * @return Stack
     */
    public function stack(array $tasks = null)
    {
        return $this->createInstance('stack', array($tasks));
    }

    /**
     * Create a watch on certain assets performing some action when they change.
     * @param null|object $binding
     * @return Watch
     */
    public function watch($binding = null)
    {
        return $this->createInstance('watch', ($binding)? array($binding) : array());
    }

    /**
     * Concat a set of files.
     * @param array $files
     * @return Concat
     */
    public function concat(array $files)
    {
        return $this->createInstance('concat', array($files));
    }

    /**
     * Delete a file.
     * @param $file
     * @return Delete
     */
    public function delete($file)
    {
        return $this->createInstance('delete', array($file));
    }

    /**
     * Delete a file (alias to 'delete').
     * @param $file
     * @return Delete
     */
    public function rm($file)
    {
        return $this->delete($file);
    }

    /**
     * Replace a file.
     * @param $file
     * @return Replace
     */
    public function replace($file)
    {
        return $this->createInstance('replace', array($file));
    }

    /**
     * Write to a file.
     * @param $file
     * @return Write
     */
    public function write($file)
    {
        return $this->createInstance('write', array($file));
    }

    /**
     * Clean the contents of a directory (removing all files and subdirectories).
     * @param $dirs
     * @return CleanDir
     */
    public function cleanDir($dirs)
    {
        return $this->createInstance('cleanDir', array($dirs));
    }

    /**
     * Copy a Directory.
     * @param $dirs
     * @return CopyDir
     */
    public function copyDir($dirs)
    {
        return $this->createInstance('copyDir', array($dirs));
    }

    /**
     * Delete a directory.
     * @param $dirs
     * @return DeleteDir
     */
    public function deleteDir($dirs)
    {
        return $this->createInstance('deleteDir', array($dirs));
    }

    /**
     * Delete a directory (alias to 'deleteDir').
     * @param $dirs
     * @return DeleteDir
     */
    public function rmdir($dirs)
    {
        return $this->deleteDir($dirs);
    }

    /**
     * Create a directory.
     * @param $dirs
     * @return MakeDir
     */
    public function mkdir($dirs)
    {
        return $this->createInstance('makeDir', array($dirs));
    }

    /**
     * Mirror a directory.
     * @param $dirs
     * @return MirrorDir
     */
    public function mirrorDir($dirs)
    {
        return $this->createInstance('mirrorDir', array($dirs));
    }

    /**
     * Create a Tarball.
     * @param $archivePath string Destination path of the tarball
     * @return CreateTarball
     */
    public function createTarball($archivePath)
    {
        return $this->createInstance('createTarball', array($archivePath));
    }

    /**
     * Creates an instance of the task object.
     *
     * This provides some indirection between the actual task implementation and the factory
     * function (allowing tasks to be overridden when running Unit Tests).
     *
     * @param $key string Name of the tasks
     * @param array $arguments Actual argument signature of the constructor.
     * @return Task an implementation of Task corresponding to the key.
     */
    function createInstance($key, array $arguments = array())
    {
        Preconditions::assertArrayContainsKey($this->mapping, $key);
        Preconditions::assertNotNull($arguments);

        $instance = null;
        $classOrInstanceOrFn = $this->mapping[$key];

        // For testing purposes.
        if (is_object($classOrInstanceOrFn))
        {
            $instance = $classOrInstanceOrFn;
        }
        // For testing purposes.
        else if (is_callable($classOrInstanceOrFn))
        {
            $instance = call_user_func_array($classOrInstanceOrFn, $arguments);
        }
        else
        {
            $refClass = new \ReflectionClass($classOrInstanceOrFn);
            $instance = $refClass->newInstanceArgs($arguments);
        }

        $instance->init($this->configuration, $this->communicator);

        return $instance;
    }
}