<?php
/*
 * This file is part of the Behind-Project (https://github.com/glady/Behind).
 *
 * (c) Mike Gladysch <mail@mike-gladysch.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace glady\Behind\Dependency\Configuration;

/**
 * Class DependencyConfiguration
 * @package glady\Behind\Dependency\Configuration
 */
class DependencyConfiguration
{

    const CONFIG_CLASS = 'class';
    const CONFIG_WRAPPER = 'wrapper';
    const CONFIG_INITIALIZE_CALLBACK = 'initializeCallback';
    const CONFIG_SHARED = 'shared';

    const DEFAULT_CLASS = null;
    const DEFAULT_WRAPPER = null;
    const DEFAULT_SHARED = true;
    const DEFAULT_INITIALIZE_CALLBACK = null;

    /** @var string|null */
    private $class = self::DEFAULT_CLASS;

    /** @var string|null */
    private $wrapper = self::DEFAULT_WRAPPER;

    /** @var bool */
    private $shared = self::DEFAULT_SHARED;

    /** @var callable|null */
    private $initializeCallback = self::DEFAULT_INITIALIZE_CALLBACK;


    /**
     * @param array $array
     * @return DependencyConfiguration
     */
    public static function createFromArray(array $array)
    {
        $class = self::getFromArray($array, self::CONFIG_CLASS, self::DEFAULT_CLASS);
        $wrapper = self::getFromArray($array, self::CONFIG_WRAPPER, self::DEFAULT_WRAPPER);
        $shared = self::getFromArray($array, self::CONFIG_SHARED, self::DEFAULT_SHARED);
        $initializeCallback = self::getFromArray($array, self::CONFIG_INITIALIZE_CALLBACK, self::DEFAULT_INITIALIZE_CALLBACK);

        $instance = new self();
        $instance->setClass($class);
        $instance->setWrapper($wrapper);
        $instance->setShared($shared);
        $instance->setInitializeCallback($initializeCallback);

        return $instance;
    }


    /**
     * @param array  $array
     * @param string $key
     * @param mixed  $default
     * @return mixed|null
     */
    private static function getFromArray(array $array, $key, $default = null)
    {
        return isset($array[$key])
            ? $array[$key]
            : $default;
    }


    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }


    /**
     * @param string $class
     */
    public function setClass($class = null)
    {
        $this->class = $class;
    }


    /**
     * @return bool
     */
    public function isShared()
    {
        return $this->shared;
    }


    /**
     * @param bool $shared
     */
    public function setShared($shared = true)
    {
        $this->shared = $shared === true;
    }


    /**
     * @return callable|null
     */
    public function getInitializeCallback()
    {
        return $this->initializeCallback;
    }


    /**
     * @param callable|null $initializeCallback
     */
    public function setInitializeCallback($initializeCallback)
    {
        $this->initializeCallback = $initializeCallback;
    }


    /**
     * @return null|string
     */
    public function getWrapper()
    {
        return $this->wrapper;
    }


    /**
     * @param null|string $wrapper
     */
    public function setWrapper($wrapper = null)
    {
        $this->wrapper = $wrapper;
    }
}
