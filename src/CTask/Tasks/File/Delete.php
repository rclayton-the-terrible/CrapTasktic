<?php

namespace CTask\Tasks\File;


use CTask\Result;
use CTask\Tasks\BaseTask;

class Delete extends BaseTask
{
    protected $filename;

    public function __construct($filename)
    {
        $this->filename = $filename;
    }

    /**
     * Execute the task and return the result.  The result must be of type CTask\Result;
     * if it is not, you are a very bad person.
     * @return Result
     */
    public function run()
    {
        $result = null;

        if (file_exists($this->filename))
        {
            $result = unlink($this->filename);
        }
        else
        {
            if (!$this->ignore) return Result::error('File does not exist.');
        }

        return ($result)? Result::success() : Result::error('Failed to delete file.');
    }
}