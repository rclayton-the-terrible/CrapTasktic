<?php


/**
The MIT License (MIT)

Copyright (c) 2014 Codegyre developers team

Permission is hereby granted, free of charge, to any person obtaining a copy of
this software and associated documentation files (the "Software"), to deal in
the Software without restriction, including without limitation the rights to
use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of
the Software, and to permit persons to whom the Software is furnished to do so,
subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

namespace CTask\Tasks\FileSystem;

use CTask\Result;

/**
 * Copies one dir into another
 */
class CopyDir extends BaseDir
{
    /** @var int $chmod */
    protected $chmod = 0755;

    /**
     * Sets the default folder permissions for the destination if it doesn't exist
     *
     * @link http://en.wikipedia.org/wiki/Chmod
     * @link http://php.net/manual/en/function.mkdir.php
     * @link http://php.net/manual/en/function.chmod.php
     * @param int $value
     * @return $this
     */
    public function dirPermissions($value)
    {
        $this->chmod = (int)$value;
        return $this;
    }
    /**
     * Copies a directory to another location.
     *
     * @param string $src Source directory
     * @param string $dst Destination directory
     * @throws \Exception
     * @return void
     */
    protected function copyDir($src, $dst)
    {
        $dir = @opendir($src);
        if (false === $dir) {
            throw new \Exception("Cannot open source directory '" . $src . "'");
        }
        if (!is_dir($dst)) {
            mkdir($dst, $this->chmod, true);
        }
        while (false !== ($file = readdir($dir))) {
            if (($file !== '.') && ($file !== '..')) {
                $srcFile = $src . '/' . $file;
                $destFile = $dst . '/' . $file;
                if (is_dir($srcFile)) {
                    $this->copyDir($srcFile, $destFile);
                } else {
                    copy($srcFile, $destFile);
                }
            }
        }
        closedir($dir);
    }

    /**
     * Execute the task and return the result.  The result must be of type CTask\Result;
     * if it is not, you are a very bad person.
     * @return Result
     */
    public function run()
    {
        foreach ($this->dirs as $src => $dst) {
            $this->copyDir($src, $dst);
            $this->printTaskInfo("Copied from <info>$src</info> to <info>$dst</info>");
        }
        return Result::success();
    }
}