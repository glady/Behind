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
use glady\Behind\TestFramework\UnitTest\Helper\Mocker;
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
     *
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
     *
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


    public function testEvents()
    {
        $me = $this;
        $classLoader = new ClassLoader();
        $calledEvents = array();
        $classLoader->on($classLoader::ON_ALL,
            function ($loader, $eventName, $eventData) use ($me, $classLoader, &$calledEvents) {
                $me->assertInstanceOf('\glady\Behind\ClassLoader\ClassLoader', $loader);
                $me->assertSame($classLoader, $loader);
                $me->assertTrue(is_string($eventName));
                $me->assertTrue(is_array($eventData));
                $calledEvents[] = $eventName;
            }
        );
        $classLoader->loadClass('SomeClass');
        $this->assertSame(array($classLoader::ON_BEFORE_LOAD, $classLoader::ON_AFTER_LOAD), $calledEvents);
        // TODO: add test for remaining events too
    }


    public function testRegister()
    {
        $registered = array();
        $this->mockSplAutoloadRegister($registered);

        $this->assertCount(0, $registered);
        $classLoader = ClassLoader::registerAutoLoader();
        $this->assertCount(1, $registered);
        $this->assertSame($classLoader, $registered[0][0]);
        $this->assertSame('loadClass', $registered[0][1]);
    }


    public function testRegisterInstance()
    {
        $registered = array();
        $this->mockSplAutoloadRegister($registered);

        $classLoader = new ClassLoader();
        $this->assertCount(0, $registered);

        $return = ClassLoader::registerAutoLoader($classLoader);
        $this->assertSame($classLoader, $return);

        $this->assertCount(1, $registered);
        $this->assertSame($classLoader, $registered[0][0]);
        $this->assertSame('loadClass', $registered[0][1]);
    }


    public function testRegisterOnInstance()
    {
        $registered = array();
        $this->mockSplAutoloadRegister($registered);

        $classLoader = new ClassLoader();
        $this->assertCount(0, $registered);

        $classLoader->register();

        $this->assertCount(1, $registered);
        $this->assertSame($classLoader, $registered[0][0]);
        $this->assertSame('loadClass', $registered[0][1]);
    }


    /**
     * @param array &$registered - reference of an array for remembering registered callables
     * @throws \Exception
     */
    private function mockSplAutoloadRegister(array &$registered)
    {
        $mocker = new Mocker();
        $mocker->mockGlobalFunctionForNamespace(
            'spl_autoload_register',
            'glady\Behind\ClassLoader',
            function ($callable) use (&$registered) {
                $registered[] = $callable;
            }
        );

        // register cleanup
        $this->onTearDown(
            array($mocker, 'removeMockOfGlobalFunctionForNamespace'),
            array('spl_autoload_register', 'glady\Behind\ClassLoader')
        );

        // register check for cleanup
        $me = $this;
        $this->onTearDown(
            function() use ($mocker, $me) {
                // validate that remove was successful
                $me->assertFalse($mocker->isGlobalFunctionMockedForNamespace(
                    'spl_autoload_register',
                    'glady\Behind\ClassLoader'
                ), 'Global function is still mocked!');
            }
        );
    }
}
