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
 * Class ClassLoaderEventTest
 * @package glady\Behind\ClassLoader\test
 */
class ClassLoaderEventTest extends TestCase
{
    protected $className = ClassLoader::CLASSNAME;


    /**
     * @param ClassLoader $classLoader
     * @param string      $event
     * @param array       $data
     */
    public function fireEvent(ClassLoader $classLoader, $event, array $data = array())
    {
        $reflection = new Reflection();
        $method = $reflection->getMethod($this->className, 'fire');
        $method->invoke($classLoader, $event, $data);
    }


    public function testEventCalled()
    {
        $classLoader = new ClassLoader();
        $me = $this;
        $called = 0;
        $classLoader->on('someEvent', function(ClassLoader $loader, $eventName, array $data) use ($me, &$called) {
            $called++;
            $me->assertEquals('someEvent', $eventName);
            $me->assertEquals(array('test' => true), $data);
        });

        $this->assertEquals(0, $called);
        $this->fireEvent($classLoader, 'someEvent', array('test' => true));
        $this->assertEquals(1, $called);
        $this->fireEvent($classLoader, 'someEvent', array('test' => true));
        $this->assertEquals(2, $called);
    }


    public function testOtherEventCalled()
    {
        $classLoader = new ClassLoader();
        $me = $this;
        $called = 0;
        $classLoader->on('someEvent', function(ClassLoader $loader, $eventName, array $data) use ($me, &$called) {
            $called++;
        });

        $this->assertEquals(0, $called);
        $this->fireEvent($classLoader, 'otherEvent', array('test' => true));
        $this->assertEquals(0, $called);
        $this->fireEvent($classLoader, 'myEvent', array('test' => true));
        $this->assertEquals(0, $called);
    }


    public function testTwoEventsCalled()
    {
        $classLoader = new ClassLoader();
        $called = 0;
        $classLoader->on('someEvent', function() use (&$called) {
            $called++;
        });
        $classLoader->on('someEvent', function() use (&$called) {
            $called++;
        });

        $this->assertEquals(0, $called);
        $this->fireEvent($classLoader, 'someEvent', array('test' => true));
        $this->assertEquals(2, $called);
        $this->fireEvent($classLoader, 'someEvent', array('test' => true));
        $this->assertEquals(4, $called);
    }


    public function testTwoEventsCalledFirstBreakingEvent()
    {
        $classLoader = new ClassLoader();
        $called = 0;
        $classLoader->on('someEvent', function() use (&$called) {
            $called++;
            return false;
        }, array('breakEventOnReturnFalse' => true));
        $classLoader->on('someEvent', function() use (&$called) {
            $called++;
        });

        $this->assertEquals(0, $called);
        $this->fireEvent($classLoader, 'someEvent', array('test' => true));
        $this->assertEquals(1, $called);
        $this->fireEvent($classLoader, 'someEvent', array('test' => true));
        $this->assertEquals(2, $called);
    }


    public function testTwoEventsCalledFirstBreakingEventNotReturnsFalse()
    {
        $classLoader = new ClassLoader();
        $called = 0;
        $classLoader->on('someEvent', function() use (&$called) {
            $called++;
            return null;
        }, array('breakEventOnReturnFalse' => true));
        $classLoader->on('someEvent', function() use (&$called) {
            $called++;
        });

        $this->assertEquals(0, $called);
        $this->fireEvent($classLoader, 'someEvent', array('test' => true));
        $this->assertEquals(2, $called);
        $this->fireEvent($classLoader, 'someEvent', array('test' => true));
        $this->assertEquals(4, $called);
    }


    public function testTwoEventsCalledFirstSingle()
    {
        $classLoader = new ClassLoader();
        $called = 0;
        $classLoader->on('someEvent', function() use (&$called) {
            $called++;
        }, array('single' => true));
        $classLoader->on('someEvent', function() use (&$called) {
            $called++;
        });

        $this->assertEquals(0, $called);
        $this->fireEvent($classLoader, 'someEvent', array('test' => true));
        $this->assertEquals(2, $called); // both
        $this->fireEvent($classLoader, 'someEvent', array('test' => true));
        $this->assertEquals(3, $called); // only second
    }


    public function testNamedEventCanBeRemoved()
    {
        $classLoader = new ClassLoader();
        $called = 0;
        $classLoader->on('someEvent', function() use (&$called) {
            $called++;
        }, array(), 'myEvent');

        $this->assertEquals(0, $called);
        $this->fireEvent($classLoader, 'someEvent', array('test' => true));
        $this->assertEquals(1, $called);
        $classLoader->un('someEvent', 'myEvent');
        $this->fireEvent($classLoader, 'someEvent', array('test' => true));
        $this->assertEquals(1, $called);
    }


    public function testAllEventFiredOnEachInternalEvent()
    {
        $classLoader = new ClassLoader();
        $called = 0;
        $classLoader->on('someEvent', function() use (&$called) {
            $called++;
        });
        $classLoader->on($classLoader::ON_AFTER_REQUIRE, function() use (&$called) {
            $called++;
        });
        $classLoader->on($classLoader::ON_ALL, function() use (&$called) {
            $called++;
        });

        $this->assertEquals(0, $called);
        $this->fireEvent($classLoader, 'someEvent', array('test' => true));
        $this->assertEquals(1, $called); // only someEvent
        $this->fireEvent($classLoader, 'otherEvent', array('test' => true));
        $this->assertEquals(1, $called); // no event
        $this->fireEvent($classLoader, $classLoader::ON_BEFORE_LOAD, array('test' => true));
        $this->assertEquals(2, $called); // only all
        $this->fireEvent($classLoader, $classLoader::ON_RULE_DOES_NOT_MATCH, array('test' => true));
        $this->assertEquals(3, $called); // only all
        $this->fireEvent($classLoader, $classLoader::ON_BEFORE_REQUIRE, array('test' => true));
        $this->assertEquals(4, $called); // only all
        $this->fireEvent($classLoader, $classLoader::ON_AFTER_LOAD, array('test' => true));
        $this->assertEquals(5, $called); // only all
        $this->fireEvent($classLoader, $classLoader::ON_AFTER_REQUIRE, array('test' => true));
        $this->assertEquals(7, $called); // event and all
    }
}
