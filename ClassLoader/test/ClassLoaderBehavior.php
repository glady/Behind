<?php

namespace glady\Behind\ClassLoader\test;

use glady\Behind\ClassLoader\ClassLoader;
use glady\Behind\TestFramework\UnitTest\TestCase;

abstract class ClassLoaderBehavior extends TestCase
{
    /** @var string */
    protected $className = '\glady\Behind\ClassLoader\ClassLoader';

    /** @var MockCLassLoader */
    private $classLoader = null;

    /** @var string */
    private $loadedClassName = null;

    /** @var int */
    private $numberOfRules = 0;


    /**
     * creates a not configured instance of class loader
     */
    protected function givenIHaveAClassLoader()
    {
        $this->classLoader = new MockClassLoader();
        // check that mock is inherited class of object we want to test!
        $this->assertInstanceOf(ClassLoader::CLASSNAME, $this->classLoader);
    }


    protected function givenIHaveNotConfiguredClassLoader()
    {
        // do nothing ;)
    }

    protected function givenIHaveASeparatorRuleWith_AsSeparatorOnDirectory($separator, $directory)
    {
        $this->numberOfRules++;
        $this->classLoader->addSeparatorClassLoaderRule($directory, $separator);
    }

    protected function givenIHaveANamespaceRuleOnDirectory($directory)
    {
        $this->numberOfRules++;
        $this->classLoader->addNamespaceClassLoaderRule($directory);
    }

    protected function whenITryToLoadExistingClass($className)
    {
        $this->loadedClassName = $className;
        $this->classLoader->loadClass($className);
    }


    protected function theNoFatalErrorShouldOccur()
    {
        $this->assertTrue(true);
    }


    protected function thenIShouldNotHaveTriedToLoadAnything()
    {
        $eventsFired = $this->classLoader->_eventsFired;

        $state = array(
            ClassLoader::LOAD_STATE_LOADED     => false,
            ClassLoader::LOAD_STATE_CLASS_NAME => $this->loadedClassName,
            ClassLoader::LOAD_STATE_FILE_NAME  => null
        );

        // before load and after load should be filled, exactly ONE event with $state!
        $this->assertArrayHasKey(ClassLoader::ON_BEFORE_LOAD, $eventsFired);
        $this->assertEquals(array($state), $eventsFired[ClassLoader::ON_BEFORE_LOAD]);
        $this->assertArrayHasKey(ClassLoader::ON_AFTER_LOAD, $eventsFired);
        $this->assertEquals(array($state), $eventsFired[ClassLoader::ON_AFTER_LOAD]);

        // but no other event should be fired
        $this->assertArrayNotHasKey(ClassLoader::ON_BEFORE_REQUIRE, $eventsFired);
        $this->assertArrayNotHasKey(ClassLoader::ON_AFTER_REQUIRE, $eventsFired);
        $this->assertArrayNotHasKey(ClassLoader::ON_RULE_DOES_NOT_MATCH, $eventsFired);
    }

    protected function thenIShouldHaveLoadedFile($file)
    {
        $eventsFired = $this->classLoader->_eventsFired;

        $state = array(
            ClassLoader::LOAD_STATE_LOADED     => false,
            ClassLoader::LOAD_STATE_CLASS_NAME => $this->loadedClassName,
            ClassLoader::LOAD_STATE_FILE_NAME  => null
        );

        // before load should be fired with "not loaded" state
        $this->assertArrayHasKey(ClassLoader::ON_BEFORE_LOAD, $eventsFired);
        $this->assertEquals(array($state), $eventsFired[ClassLoader::ON_BEFORE_LOAD]);

        // after load should be fired with "file loaded" state
        $state[ClassLoader::LOAD_STATE_LOADED] = true;
        $state[ClassLoader::LOAD_STATE_FILE_NAME] = realpath($file);
        $this->assertArrayHasKey(ClassLoader::ON_AFTER_LOAD, $eventsFired);
        $this->assertEquals(array($state), $eventsFired[ClassLoader::ON_AFTER_LOAD]);

        $this->assertArrayHasKey(ClassLoader::ON_BEFORE_REQUIRE, $eventsFired);
        $this->assertArrayHasKey(ClassLoader::ON_AFTER_REQUIRE, $eventsFired);

        if ($this->numberOfRules === 1) {
            // only event not fired should be, that rule does not match
            $this->assertArrayNotHasKey(ClassLoader::ON_RULE_DOES_NOT_MATCH, $eventsFired);
        }

    }

    public function __call($name, $arguments)
    {
        $this->markTestIncomplete("Behavior function '$name' is not implemented yet");
    }
}


class MockClassLoader extends ClassLoader
{
    /** @var array */
    public $_eventsFired = array();

    /**
     * Constructor
     */
    public function __construct()
    {
//         parent::__construct();
        $me = $this;
        $this->on(self::ON_ALL, function ($classLoader, $eventName, $eventData) use ($me) {
            $me->_eventsFired[$eventName][] = $eventData;
        });
    }

}