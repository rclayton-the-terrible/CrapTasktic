<?php

namespace CTask\Tasks\File;


use CTask\Result;
use CTask\Tasks\BaseTask;
use CTask\Tasks\ParamsHelper;

class Replace extends BaseTask
{
    protected $filename;
    protected $from;
    protected $to;
    protected $regex;

    public function __construct($filename)
    {
        $this->filename = $filename;
    }

    function run()
    {
        if (!file_exists($this->filename)) {
            $this->printTaskError("File {$this->filename} does not exist");
            return false;
        }
        $text = file_get_contents($this->filename);
        if ($this->regex) {
            $text = preg_replace($this->regex, $this->to, $text, -1, $count);
        } else {
            $text = str_replace($this->from, $this->to, $text, $count);
        }
        $res = file_put_contents($this->filename, $text);
        if ($res === false) {
            return Result::error("Error writing to file {$this->filename}.");
        }
        $this->printTaskSuccess("<info>{$this->filename}</info> updated. $count items replaced");
        return Result::success(array('replaced' => $count));
    }

    function __call($name, $arguments)
    {
        return ParamsHelper::callDelegate($this, $name, $arguments);
    }
}

?>