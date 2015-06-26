<?php
/*
 * This file is part of the Behind-Project (https://github.com/glady/Behind).
 *
 * (c) Mike Gladysch <mail@mike-gladysch.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace glady\Behind\ClassLoader;

use glady\Behind\Utils\File\File;
use glady\Behind\Utils\File\Iterator;

/**
 * Class ClassMapGenerator
 * @package glady\Behind\ClassLoader
 */
class ClassMapGenerator
{
    /** @var array */
    private $paths = array();

    /** @var bool */
    private $acceptMultipleClassesPerFile = false;


    public function generate()
    {
        $map = array();
        $me  = $this;
        foreach ($this->paths as $path) {
            $fileIterator = new Iterator($path);
            $fileIterator->forEachFile(function(File $fileInfo) use ($me, &$map) {
                $tokens = token_get_all($fileInfo->getContent());
                foreach ($tokens as $i => $token) {
                    if ($me->isTokenClass($token[0])) {
                        $class = $tokens[$i + 2][1];
                        $map[$class] = $fileInfo->getRealPath();
                        if (!$me->acceptsMultipleClassesPerFile()) {
                            return;
                        }
                    }
                }
            });
        }
        return $map;
    }


    public function addPath($path)
    {
        $this->paths[] = $path;
    }


    public function isTokenClass($token)
    {
        return $token === T_CLASS
            || $token === T_INTERFACE
            || defined('T_TRAIT') && $token === T_TRAIT;
    }


    /**
     * @return bool
     */
    public function acceptsMultipleClassesPerFile()
    {
        return $this->acceptMultipleClassesPerFile;
    }


    /**
     * @param bool $acceptMultipleClassesPerFile
     */
    public function acceptMultipleClassesPerFile($acceptMultipleClassesPerFile = true)
    {
        $this->acceptMultipleClassesPerFile = $acceptMultipleClassesPerFile === true;
    }
}
