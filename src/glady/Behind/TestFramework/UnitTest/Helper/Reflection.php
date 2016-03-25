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
     * @param $className
     * @return ReflectionClass
     */
    public function getClass($className)
    {
        return new ReflectionClass($className);
    }


    /**
     * @param $className
     * @param $methodName
     * @return ReflectionMethod
     */
    public function getMethod($className, $methodName)
    {
        $method = $this->getClass($className)->getMethod($methodName);
        $method->setAccessible(true);
        return $method;
    }
}
