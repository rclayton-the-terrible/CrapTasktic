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

class CleanDir extends BaseDir
{
    protected function emptyDir($path)
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($iterator as $path) {
            if ($path->isDir()) {
                $dir = (string)$path;
                if (basename($dir) === '.' || basename($dir) === '..') {
                    continue;
                }
                $this->fs->remove($dir);
            } else {
                $file = (string)$path;
                if (basename($file) === '.gitignore' || basename($file) === '.gitkeep') {
                    continue;
                }
                $this->fs->remove($file);
            }
        }
    }

    /**
     * Execute the task and return the result.  The result must be of type CTask\Result;
     * if it is not, you are a very bad person.
     * @return Result
     */
    public function run()
    {
        foreach ($this->dirs as $dir) {
            $this->emptyDir($dir);
            $this->printTaskInfo("cleaned <info>$dir</info>");
        }
        return Result::success($this);
    }
}

?>