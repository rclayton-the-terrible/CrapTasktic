<?php

namespace CTask\Tasks\Base;


use CTask\Result;
use CTask\Tasks\ExecTask;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Process;

class ParallelExec extends ExecTask
{
    protected $processes = array();
    protected $timeout = null;
    protected $idleTimeout = null;

    public function process($command)
    {
        $this->processes[] = new Process($command);
        return $this;
    }

    public function run()
    {
        foreach ($this->processes as $process) {
            /** @var $process Process  **/
            $process->setIdleTimeout($this->idleTimeout);
            $process->setTimeout($this->timeout);
            $process->start();
            $this->printTaskInfo($process->getCommandLine());
        }
        $progress = new ProgressBar($this->communicator->getOutput());
        $progress->start(count($this->processes));
        $running = $this->processes;
        $progress->display();
        $this->startTimer();
        while (true) {
            foreach ($running as $k => $process) {
                try {
                    $process->checkTimeout();
                } catch (ProcessTimedOutException $e) {
                }
                if (!$process->isRunning()) {
                    $progress->advance();
                    if ($this->isPrinted) {
                        $this->communicator->write("");
                        $this->printTaskInfo("Output for <fg=white;bg=magenta> " . $process->getCommandLine()." </fg=white;bg=magenta>");
                        $this->communicator->write($process->getOutput(), OutputInterface::OUTPUT_RAW);
                        if ($process->getErrorOutput()) {
                            $this->communicator->write("<error>" . $process->getErrorOutput() . "</error>");
                        }
                    }
                    unset($running[$k]);
                }
            }
            if (empty($running)) {
                break;
            }
            usleep(1000);
        }
        $this->communicator->write("");
        $this->stopTimer();
        $errorMessage = '';
        $exitCode = 0;

        /**
         * @var $p Process
         */
        foreach ($this->processes as $p) {
            if ($p->getExitCode() === 0) continue;
            $errorMessage .= "'" . $p->getCommandLine() . "' exited with code ". $p->getExitCode()." \n";
            $exitCode = max($exitCode, $p->getExitCode());
        }
        if (!$errorMessage) $this->printTaskSuccess(count($this->processes) . " processes finished running");
        return new Result($exitCode, array('time' => $this->getExecutionTime()), $errorMessage);
    }
}

?>