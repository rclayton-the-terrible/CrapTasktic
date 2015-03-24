<?php

namespace CTask\Tasks\Base;


use CTask\Preconditions;
use CTask\Result;
use CTask\Task;
use CTask\Tasks\BaseTask;
use Exception;

class Stack extends BaseTask
{
    private $stopOnFail = true;
    private $tasks;

    public function __construct(array $tasks = null)
    {
        $this->tasks = $tasks;
    }

    public function ignoreFailures()
    {
        $this->stopOnFail = false;
    }

    public function tasks(array $tasks)
    {
        $this->tasks = $tasks;
    }

    /**
     * Execute the task and return the result.  The result must be of type CTask\Result;
     * if it is not, you are a very bad person.
     * @return Result
     * @throws Exception if ignoreFailures not used.
     */
    public function run()
    {
        Preconditions::assert($this->tasks != null && count($this->tasks) > 0);

        $results = array();

        /**
         * @var $task Task
         */
        foreach($this->tasks as $task)
        {
            Preconditions::assert($task instanceof Task, 'Supplied task to ExecStack must be an instance of CTask\Task.');

            try
            {
                $results[] = $task->run();
            }
            catch (Exception $e)
            {
                if ($this->stopOnFail) throw $e;
                $results[] = Result::error($e);
            }
        }

        return Result::success($results);
    }
}

?>