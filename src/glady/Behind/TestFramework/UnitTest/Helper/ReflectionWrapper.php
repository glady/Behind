<?php
/*
 * This file is part of the Behind-Project (https://github.com/glady/Behind).
 *
 * (c) Mike Gladysch <mail@mike-gladysch.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace glady\Behind\src\glady\Behind\TestFramework\UnitTest\Helper;

use glady\Behind\TestFramework\UnitTest\Helper\Reflection;

/**
 * Class ReflectionWrapper
 * @package glady\Behind\src\glady\Behind\TestFramework\UnitTest\Helper
 */
class ReflectionWrapper
{
    /**
     * @param object $object
     */
    public function __construct($object)
    {
        $this->original = $object;
        $this->reflection = new Reflection();
    }


    /**
     * @param string $name
     * @param array  $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        $method = $this->reflection->getMethod($this->original, $name);
        return $method->invokeArgs($this->original, $arguments);
    }
}
