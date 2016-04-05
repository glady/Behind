<?php
/*
 * This file is part of the Behind-Project (https://github.com/glady/Behind).
 *
 * (c) Mike Gladysch <mail@mike-gladysch.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace glady\Behind\TestFramework\UnitTest\Helper;

use ReflectionClass;
use ReflectionMethod;

/**
 * Class Reflection
 * @package glady\Behind\TestFramework\UnitTest\Helper
 */
class Reflection
{
    /**
     * @param string|object $class
     * @return ReflectionClass
     */
    public function getClass($class)
    {
        return new ReflectionClass($class);
    }


    /**
     * @param string|object $class
     * @param string        $methodName
     * @return ReflectionMethod
     */
    public function getMethod($class, $methodName)
    {
        $method = $this->getClass($class)->getMethod($methodName);
        $method->setAccessible(true);
        return $method;
    }
}
