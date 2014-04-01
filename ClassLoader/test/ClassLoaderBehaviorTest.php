<?php
namespace glady\Behind\ClassLoader\test;


/**
 * Class ClassLoaderBehaviorTest
 * @package glady\Behind\ClassLoader\test
 */
class ClassLoaderBehaviorTest extends ClassLoaderBehavior
{

    public function testNonRegisteredClassLoader()
    {
        $this->givenIHaveAClassLoader();
        $this->givenIHaveNotConfiguredClassLoader();
        $this->whenITryToLoadExistingClass('TestFolder_TestClass');
        $this->thenIShouldNotHaveTriedToLoadAnything();
    }


    public function testRegisteredSeparatorRuleClassLoader()
    {
        $this->givenIHaveAClassLoader();
        $this->givenIHaveASeparatorRuleWith_AsSeparatorOnDirectory('_', __DIR__ . '/testData');
        $this->whenITryToLoadExistingClass('TestFolder_TestClass');
        $this->thenIShouldHaveLoadedFile(__DIR__ . '/testData/TestFolder/TestClass.php');
    }


    public function testRegisteredNamespaceRuleClassLoader()
    {
        $this->givenIHaveAClassLoader();
        $this->givenIHaveANamespaceRuleOnDirectory(__DIR__ . '/testData');
        $this->whenITryToLoadExistingClass('\TestFolder\TestClass2');
        $this->thenIShouldHaveLoadedFile(__DIR__ . '/testData/TestFolder/TestClass2.php');
    }


    public function testRegisteredSeparatorRuleClassLoaderLoadClassTwice()
    {
        $this->testRegisteredSeparatorRuleClassLoader();
        $this->whenITryToLoadExistingClass('TestFolder_TestClass');
        $this->theNoFatalErrorShouldOccur();
    }
} 