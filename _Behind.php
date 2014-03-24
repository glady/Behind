<?php

namespace glady;

class Behind
{
    const VERSION = 'dev';


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