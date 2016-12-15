<?php
/*
 * This file is part of the Behind-Project (https://github.com/glady/Behind).
 *
 * (c) Mike Gladysch <mail@mike-gladysch.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace glady\Behind\Dependency;

/**
 * Class Registry
 * @package glady\Behind\Dependency
 */
class Registry
{
    /** @var array  */
    private $register = array();


    /**
     * @param string $key
     * @return bool
     */
    public function isRegistered($key)
    {
        return isset($this->register[$key]);
    }


    /**
     * @param string $key
     * @param mixed  $value
     */
    public function register($key, $value)
    {
        $this->register[$key] = $value;
    }


    /**
     * @param string $key
     * @return mixed
     */
    public function getRegistered($key)
    {
        return isset($this->register[$key])
            ? $this->register[$key]
            : null;
    }

}
