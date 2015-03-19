<?php

namespace CTask\Tasks\FileSystem;

use CTask\Tasks\BaseTask;
use Symfony\Component\Filesystem\Filesystem as sfFileSystem;

abstract class BaseDir extends BaseTask
{
    protected $dirs = array();
    protected $fs;

    public function __construct($dirs)
    {
        is_array($dirs)
            ? $this->dirs = $dirs
            : $this->dirs[] = $dirs;
        $this->fs = new sfFileSystem();
    }
}