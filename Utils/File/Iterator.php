<?php
/*
 * This file is part of the Behind-Project (https://github.com/glady/Behind).
 *
 * (c) Mike Gladysch <mail@mike-gladysch.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace glady\Behind\Utils\File;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Class FileSystemIterator
 * @package glady\Behind\Utils\File
 */
class Iterator
{
    private $path = __DIR__;
    private $mode = RecursiveIteratorIterator::CHILD_FIRST;


    public function __construct($path, $mode = RecursiveIteratorIterator::CHILD_FIRST)
    {
        $this->path = $path;
        $this->$mode = $mode;
    }


    public function forEachFile($callable)
    {
        foreach ($this->getIterator() as $fileInfo) {
            if ($fileInfo->isFile()) {
                $file = new File($fileInfo->openFile());
                call_user_func_array($callable, array($file));
                $file = null;
                $fileInfo = null;
            }
        }
    }


    public function forEachDirectory($callable)
    {
        foreach ($this->getIterator() as $fileInfo) {
            if ($fileInfo->isDir()) {
                $dir = new Directory($fileInfo);
                call_user_func_array($callable, array($dir));
                $dir = null;
                $fileInfo = null;
            }
        }
    }


    private function getIterator()
    {
        return new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->path, RecursiveDirectoryIterator::SKIP_DOTS),
            $this->mode
        );
    }
}
