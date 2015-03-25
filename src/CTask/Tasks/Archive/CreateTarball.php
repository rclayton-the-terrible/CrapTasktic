<?php

namespace CTask\Tasks\Archive;

use CTask\Tasks\Base\Exec;

class CreateTarball extends Exec
{
    private $method = 'z';
    private $gzipPreamble = '';
    private $archivePath;
    private $files = array();

    public function __construct($archivePath)
    {
        $this->archivePath = $archivePath;
        parent::__construct('');
    }

    public function bzip()
    {
        $this->method = 'j';
        return $this;
    }

    public function gzip($level = null)
    {
        if ($level) $this->gzipPreamble = 'GZIP=' . $level;
        $this->method = 'z';
        return $this;
    }

    public function file($file)
    {
        $this->files[] = $file;
        return $this;
    }

    public function directory($dir)
    {
        return $this->file($dir);
    }

    public function getCommand()
    {
        return trim(
            $this->gzipPreamble .
            ' tar -cf' . $this->method .
            $this->arguments .
            ' ' . $this->archivePath .
            ' ' . implode(' ', $this->files));
    }
}