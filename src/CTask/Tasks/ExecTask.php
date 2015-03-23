<?php

namespace CTask\Tasks;


use CTask\Result;
use Symfony\Component\Process\Process;

abstract class ExecTask extends BaseTask
{
    protected $isPrinted = true;
    protected $workingDirectory;
    /**
     * Is command printing its output to screen
     * @return bool
     */
    public function getPrinted()
    {
        return $this->isPrinted;
    }
    /**
     * changes working directory of command
     * @param $dir
     * @return $this
     */
    public function dir($dir)
    {
        $this->workingDirectory = $dir;
        return $this;
    }
    /**
     * Should command output be printed
     *
     * @param $arg
     * @return $this
     */
    public function printed($arg)
    {
        if (is_bool($arg)) {
            $this->isPrinted = $arg;
        }
        return $this;
    }
    /**
     * @param $command
     * @return Result
     */
    protected function executeCommand($command)
    {
        $process = new Process($command);
        $process->setTimeout(null);
        if ($this->workingDirectory) {
            $process->setWorkingDirectory($this->workingDirectory);
        }
        $this->startTimer();
        if ($this->isPrinted) {
            $process->run(function ($type, $buffer) {
                print $buffer;
            });
        } else {
            $process->run();
        }
        $this->stopTimer();
        return new Result($this, $process->getExitCode(), $process->getOutput(), array('time' => $this->getExecutionTime()));
    }
}

?>