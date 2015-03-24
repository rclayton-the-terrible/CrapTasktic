<?php

namespace CTask\Tasks\Base;

use Closure;
use CTask\Result;
use CTask\Tasks\BaseTask;
use Lurker\Event\FilesystemEvent;
use Lurker\ResourceWatcher;

class Watch extends BaseTask
{
    protected $closure;
    protected $monitor = array();
    protected $bindTo;

    public function __construct($bindTo)
    {
        $this->bindTo = $bindTo;
    }

    public function monitor($paths, Closure $callable)
    {
        if (!is_array($paths)) {
            $paths = array($paths);
        }
        $this->monitor[] = array($paths, $callable);
        return $this;
    }

    public function run()
    {
        $watcher = new ResourceWatcher();
        foreach ($this->monitor as $k => $monitor) {
            /**
             * @var $closure Closure
             */
            $closure = $monitor[1];
            $closure->bindTo($this->bindTo);
            foreach ($monitor[0] as $i => $dir) {
                $watcher->track("fs.$k.$i", $dir, FilesystemEvent::MODIFY);
                $this->printTaskInfo("watching <info>$dir</info> for changes...");
                $watcher->addListener("fs.$k.$i", $closure);
            }
        }
        $watcher->start();
        return Result::success();
    }
}

?>