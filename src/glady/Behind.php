<?php
/*
 * This file is part of the Behind-Project (https://github.com/glady/Behind).
 *
 * (c) Mike Gladysch <mail@mike-gladysch.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace glady;

use glady\Behind\ClassLoader\ClassLoader;

/**
 * Class Behind
 * @package glady
 */
class Behind
{
    const VERSION = '0.1.7';

    /** @var ClassLoader */
    private static $classLoader = null;


    /**
     * @param ClassLoader $classLoader
     */
    public static function setClassLoader(ClassLoader $classLoader)
    {
        static::$classLoader = $classLoader;
    }


    /**
     * @return ClassLoader
     */
    public static function getClassLoader()
    {
        return static::$classLoader;
    }


    /**
     * @return string
     */
    public static function getOperatingSystem()
    {
        return PHP_OS;
    }


    /**
     * @param string $os
     * @return bool
     */
    public static function isOperatingSystem($os)
    {
        return $os === self::getOperatingSystem();
    }


    /**
     * @return bool
     */
    public static function isCli()
    {
        return PHP_SAPI === 'cli';
    }


    /**
     * @param int|string $version   can be major version (int) or version string 'major.minor[.release]'
     * @param int        $minor     [optional]
     * @param int        $release   [optional]
     * @return bool
     */
    public static function checkPhpVersion($version, $minor = 0, $release = 0)
    {
        if (is_int($version)) {
            $major = $version;
        }
        else {
            $version = explode('.', $version);
            $major = $version[0];
            if (isset($version[1])) {
                $minor = $version[1];
            }
            if (isset($version[2])) {
                $release = $version[2];
            }
        }
        return PHP_MAJOR_VERSION > $major
            || PHP_MAJOR_VERSION == $major && PHP_MINOR_VERSION > $minor
            || PHP_MAJOR_VERSION == $major && PHP_MINOR_VERSION == $minor && PHP_RELEASE_VERSION >= $release;
    }
}
