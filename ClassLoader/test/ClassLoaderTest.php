<?php

namespace glady\Behind\ClassLoader\test;

require_once __DIR__ . '/../ClassLoader.php';
require_once __DIR__ . '/../../TestFramework/UnitTest/TestCase.php';

use glady\Behind\ClassLoader\ClassLoader;
use glady\Behind\TestFrameWork\UnitTest\TestCase;

class ClassLoaderTest extends TestCase
{
    public function testClassExists()
    {
        $this->assertTrue(class_exists('\glady\Behind\ClassLoader\ClassLoader', false));
    }


    /**
     * @dataProvider provideLoad
     * @dependsOn testClassExists
     */
    public function testLoad($type, $className, $loadSuccess, $loadedFileName = null)
    {
        $cl = $this->getClassLoaderByType($type);

        $me = $this;

        $afterLoadTestClosure = function ($cl, $e, $data) use ($me, $className, $loadSuccess, $loadedFileName) {
            $me->assertEquals($className, $data[$cl::LOAD_STATE_CLASS_NAME]);
            $me->assertEquals($loadSuccess, $data[$cl::LOAD_STATE_LOADED]);
            if ($loadSuccess) {
                $me->assertEquals(realpath($loadedFileName), realpath($data[$cl::LOAD_STATE_FILE_NAME]));
            }
        };

        // add event based test closure
        $cl->on($cl::ON_AFTER_LOAD, $afterLoadTestClosure);

        // call load
        $cl->loadClass($className);
    }


    public function provideLoad()
    {
        return array(
            array('separator', 'TestFolder_TestClass4', false), // class file included but defines '\TestFolder\TestClass4'!
            array('namespace', '\TestFolder\TestClass3', false),
            array('namespace', '\TestFolder\TestClass2', true, __DIR__ . '/testData/TestFolder/TestClass2.php'),
            array('separator', 'TestFolder_TestClass', true, __DIR__ . '/testData/TestFolder/TestClass.php'),

            array('namespace', '\TestFolder\SomeInvalidClass', false),
            array('separator', 'TestFolder_SomeInvalidClass', false),
        );
    }


    /**
     * @param $type
     * @return ClassLoader
     */
    private function getClassLoaderByType($type)
    {
        $cl = new ClassLoader();
        switch ($type) {
            case 'namespace':
                $cl->addNamespaceClassLoaderRule(__DIR__ . '/testData');
                break;

            case 'separator':
                $cl->addSeparatorClassLoaderRule(__DIR__ . '/testData', '_');
                break;

            default:
                $this->fail("invalid loader type '$type' given");
                return $cl;
        }
        return $cl;
    }
}