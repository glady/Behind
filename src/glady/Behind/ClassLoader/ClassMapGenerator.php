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
use RuntimeException;

/**
 * Class ClassMapGenerator
 * @package glady\Behind\ClassLoader
 */
class ClassMapGenerator
{
    /** @var string */
    private $basePath = '';

    /** @var array */
    private $paths = array();

    /** @var bool */
    private $acceptMultipleClassesPerFile = false;

    /** @var bool */
    private $throwErrorIfClassFoundTwice = false;

    /** @var string|null */
    private $fileNamePattern = null;


    /**
     * @param string $fileNamePattern
     * @return array
     * @throws RuntimeException
     */
    public function generate($fileNamePattern = null)
    {
        $map = array();
        $me  = $this;
        foreach ($this->paths as $path) {
            $fileIterator = new Iterator($path);
            $fileIterator->forEachFile(function(File $file) use ($me, &$map) {
                if (!$file->isPhp()) {
                    return;
                }
                $phpCode = $file->getContent();
                $tokens = token_get_all($phpCode);
                $namespace = '';
                foreach ($tokens as $i => $token) {
                    if ($me->isTokenNamespace($token[0])) {
                        $cursor = $i + 2;
                        while ($tokens[$cursor] !== ';' && $tokens[$cursor] !== '{') {
                            $namespace .= $tokens[$cursor][1];
                            $cursor++;
                        }
                        $namespace = trim($namespace);
                    }
                    if ($me->isTokenClass($token[0])) {
                        // TODO: move to check-fn
                        //if ($tokens[$i + 1][0] === T_WHITESPACE && $tokens[$i + 2][0] === T_STRING) {
                        if (isset($tokens[$i + 2][1]) && trim($tokens[$i + 2][1])) {
                            $class = $tokens[$i + 2][1];
                            if ($namespace) {
                                $class = "$namespace\\$class";
                            }
                            $realPath = $file->getRealPath();
                            $realPath = str_replace("\\", '/', $realPath);
                            $relativeToPath = $me->getRelativeToPath();
                            if (!empty($relativeToPath) && strpos($realPath, $relativeToPath . '/') === 0) {
                                $mapPath = substr($realPath, strlen($relativeToPath) + 1);
                            }
                            else {
                                $mapPath = $realPath;
                            }
                            if ($me->isThrowErrorIfClassFoundTwice() && isset($map[$class])) {
                                $path1 = $map[$class];
                                $path2 = $mapPath;
                                throw new RuntimeException("Class '$class' found twice: '$path1' and '$path2'");
                            }
                            $map[$class] = $mapPath;
                            if (!$me->acceptsMultipleClassesPerFile()) {
                                return;
                            }
                        }
                    }
                }
            }, $this->getFileNamePattern($fileNamePattern));
        }
        ksort($map);
        return $map;
    }


    /**
     * @param string $basePath
     */
    public function setRelativeToPath($basePath)
    {
        $this->basePath = str_replace("\\", '/', $basePath);
    }


    /**
     * @return string
     */
    public function getRelativeToPath()
    {
        return $this->basePath;
    }


    public function setFileNamePattern($fileNamePattern = null)
    {
        $this->fileNamePattern = $fileNamePattern;
    }


    public function getFileNamePattern($fileNamePattern = null)
    {
        return $fileNamePattern
            ?: $this->fileNamePattern;
    }


    /**
     * @param string $path
     */
    public function addPath($path)
    {
        $this->paths[] = $path;
    }


    /**
     * @param int $token
     * @return bool
     */
    public function isTokenClass($token)
    {
        return $token === T_CLASS
            || $token === T_INTERFACE
            || defined('T_TRAIT') && $token === T_TRAIT;
    }


    /**
     * @param int $token
     * @return bool
     */
    public function isTokenNamespace($token)
    {
        return $token === T_NAMESPACE;
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

    /**
     * @return bool
     */
    public function isThrowErrorIfClassFoundTwice()
    {
        return $this->throwErrorIfClassFoundTwice;
    }

    /**
     * @param bool $throwErrorIfClassFoundTwice
     */
    public function setThrowErrorIfClassFoundTwice($throwErrorIfClassFoundTwice = true)
    {
        $this->throwErrorIfClassFoundTwice = $throwErrorIfClassFoundTwice === true;
    }
}
