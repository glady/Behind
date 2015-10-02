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
use SplFileInfo;

/**
 * Class FileSystemIterator
 * @package glady\Behind\Utils\File
 */
class Iterator
{
    /** @var string */
    private $path = __DIR__;

    /** @var int */
    private $mode = RecursiveIteratorIterator::CHILD_FIRST;


    /**
     * @param string $path
     * @param int    $mode
     */
    public function __construct($path, $mode = RecursiveIteratorIterator::CHILD_FIRST)
    {
        $this->path = $path;
        $this->$mode = $mode;
    }


    /**
     * @param callable $callable
     * @param string|null $fileNamePattern
     */
    public function forEachFile($callable, $fileNamePattern = null)
    {
        foreach ($this->getIterator() as $fileInfo) {
            if ($fileInfo->isFile() && $this->fileNameMatches($fileInfo, $fileNamePattern)) {
                $file = new File($fileInfo->openFile());
                call_user_func_array($callable, array($file));
                $file = null;
                $fileInfo = null;
            }
        }
    }


    /**
     * @param callable $callable
     */
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


    /**
     * @return RecursiveIteratorIterator|SplFileInfo[]
     */
    private function getIterator()
    {
        return new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->path, RecursiveDirectoryIterator::SKIP_DOTS),
            $this->mode
        );
    }

    /**
     * @param SplFileInfo $fileInfo
     * @param string|null $fileNamePattern
     * @return bool
     */
    protected function fileNameMatches(SplFileInfo $fileInfo, $fileNamePattern = null)
    {
        return $fileNamePattern === null || preg_match($fileNamePattern, $fileInfo->getRealPath());
    }
}
