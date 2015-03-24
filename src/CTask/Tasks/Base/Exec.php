<?php

namespace CTask\Tasks\Base;


use CTask\Result;
use CTask\Tasks\ExecTask;
use Symfony\Component\Process\Process;

class Exec extends ExecTask
{
    static $instances = array();

    protected $command;
    protected $background = false;
    protected $timeout = null;
    protected $idleTimeout = null;
    protected $env = null;
    protected $arguments = '';

    /**
     * @var Process
     */
    protected $process;

    public function __construct($command)
    {
        $this->command = $command;
    }

    public function getCommand()
    {
        return trim($this->command . $this->arguments);
    }

    /**
     * Pass argument to executable
     *
     * @param $arg
     * @return $this
     */
    public function arg($arg)
    {
        return $this->args($arg);
    }
    /**
     * Pass methods parameters as arguments to executable
     *
     * @param $args
     * @return $this
     */
    public function args($args)
    {
        if (!is_array($args)) {
            $args = func_get_args();
        }
        $this->arguments .= " ".implode(' ', $args);
        return $this;
    }
    /**
     * Pass option to executable. Options are prefixed with `--` , value can be provided in second parameter
     *
     * @param $option
     * @param null $value
     * @return $this
     */
    public function option($option, $value = null)
    {
        if ($option !== null and strpos($option, '-') !== 0) {
            $option = "--$option";
        }
        $this->arguments .= null == $option ? '' : " " . $option;
        $this->arguments .= null == $value ? '' : " " . $value;
        return $this;
    }

    /**
     * Executes command in background mode (asynchronously)
     *
     * @return $this
     */
    public function background()
    {
        self::$instances[] = $this;
        $this->background = true;
        return $this;
    }

    /**
     * Stop command if it runs longer then $timeout in seconds
     *
     * @param $timeout
     * @return $this
     */
    public function timeout($timeout)
    {
        $this->timeout = $timeout;
        return $this;
    }

    /**
     * Stops command if it does not output something for a while
     *
     * @param $timeout
     * @return $this
     */
    public function idleTimeout($timeout)
    {
        $this->idleTimeout = $timeout;
        return $this;
    }
    /**
     * Sets the environment variables for the command
     *
     * @param $env
     * @return $this
     */
    public function env(array $env)
    {
        $this->env = $env;
        return $this;
    }

    public function __destruct()
    {
        $this->stop();
    }

    protected function stop()
    {
        if ($this->background && $this->process->isRunning()) {
            $this->process->stop();
            $this->printTaskInfo("stopped <info>".$this->getCommand()."</info>");
        }
    }

    public function run()
    {
        $command = $this->getCommand();
        $dir = $this->workingDirectory ? " in " . $this->workingDirectory : "";
        $this->printTaskInfo("running <info>{$command}</info>$dir");
        $this->process = new Process($command);
        $this->process->setTimeout($this->timeout);
        $this->process->setIdleTimeout($this->idleTimeout);
        $this->process->setWorkingDirectory($this->workingDirectory);
        if (isset($this->env)) {
            $this->process->setEnv($this->env);
        }
        if (!$this->background and !$this->isPrinted) {
            $this->startTimer();
            $this->process->run();
            $this->stopTimer();
            return new Result(
                $this->process->getExitCode(),
                array('output' => $this->process->getOutput(), 'time' => $this->getExecutionTime()),
                null);
        }
        if (!$this->background and $this->isPrinted) {
            $this->startTimer();
            $this->process->run(
                function ($type, $buffer) {
                    print($buffer);
                }
            );
            $this->stopTimer();
            return new Result(
                $this->process->getExitCode(),
                array('output' => $this->process->getOutput(), 'time' => $this->getExecutionTime()),
                null);
        }
        try {
            $this->process->start();
        } catch (\Exception $e) {
            return Result::error($this, $e);
        }
        return Result::success($this);
    }

    static function stopRunningJobs()
    {
        foreach (self::$instances as $instance) {
            if ($instance) unset($instance);
        }
    }
}
if (function_exists('pcntl_signal')) {
    pcntl_signal(SIGTERM, array('CTask\Tasks\Base\Exec', 'stopRunningJobs'));
}
register_shutdown_function(array('CTask\Tasks\Base\Exec', 'stopRunningJobs'));