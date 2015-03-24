<?php

namespace CTask\Tasks\FileSystem;


use CTask\Result;
use CTask\Tasks\BaseTask;

class MakeDir extends BaseTask
{
    protected $directory;
    protected $recursive = true;
    protected $mode = 0777;

    public function __construct($directory)
    {
        $this->directory = $directory;
    }

    public function mode($mode)
    {
        $this->mode = $mode;
    }

    public function notRecursive()
    {
        $this->recursive = false;
    }

    /**
     * Execute the task and return the result.  The result must be of type CTask\Result;
     * if it is not, you are a very bad person.
     * @return Result
     */
    public function run()
    {
        $result = null;

        if (file_exists($this->directory))
        {
            if (!$this->ignore) return Result::error('Directory already exists.');

            $result = true;
        }
        else
        {
            $result = mkdir($this->directory, $this->mode, $this->recursive);
        }

        if (!$result) return Result::error('Failed to create directory.');

        return Result::success();
    }
}

?>