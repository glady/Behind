<?php
/*
 * This file is part of the Behind-Project (https://github.com/glady/Behind).
 *
 * (c) Mike Gladysch <mail@mike-gladysch.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace glady\Behind\ClassLoader\test;

use glady\Behind\ClassLoader\ClassLoader;
use glady\Behind\TestFramework\UnitTest\Helper\Reflection;
use glady\Behind\TestFramework\UnitTest\TestCase;

/**
 * Class ClassLoaderTest
 * @package glady\Behind\ClassLoader\test
 */
class ClassLoaderTest extends TestCase
{
    protected $className = '\glady\Behind\ClassLoader\ClassLoader';


    /**
     * @dataProvider provideFileExists
     * @param string $file
     * @param bool   $expected
     */
    public function testFileExists($file, $expected)
    {
        $reflection = new Reflection();
        $method = $reflection->getMethod($this->className, 'fileExists');

        $actual = $method->invoke(new ClassLoader(), $file);
        $this->assertEquals($expected, $actual);
    }


    /**
     * @return array
     */
    public function provideFileExists()
    {
        return array(
            array(__FILE__, true),
            array(__FILE__ . '2', false)
        );
    }


    /**
     * @dataProvider provideClassExists
     * @param string $className
     * @param bool   $expected
     */
    public function testClassExists($className, $expected)
    {
        $reflection = new Reflection();
        $method = $reflection->getMethod($this->className, 'classExists');

        $actual = $method->invoke(new ClassLoader(), $className);
        $this->assertEquals($expected, $actual);
    }


    /**
     * @return array
     */
    public function provideClassExists()
    {
        eval("interface MyTestInterface {}");
        $traitsSupported = function_exists('trait_exists');
        if ($traitsSupported) {
            eval("interface MyTestTrait {}");
        }
        return array(
            array(__CLASS__, true),
            array(__CLASS__ . '2', false),
            array('\MyTestInterface', true),
            array('\MyTestInterface2', false),
            array('\MyTestTrait', $traitsSupported),
            array('\MyTestTrait2', false),
        );
    }

}
