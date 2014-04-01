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
        $this->givenIHaveAPhpFile_ThatContainsClasses('/TestFolder/TestClass.php', array('TestFolder_TestClass'));
        $this->givenIHaveNotConfiguredClassLoader();
        $this->whenITryToLoadExistingClass('TestFolder_TestClass');
        $this->thenIShouldNotHaveTriedToLoadAnything();
    }


    public function testRegisteredSeparatorRuleClassLoader()
    {
        $this->givenIHaveAClassLoader();
        $this->givenIHaveAPhpFile_ThatContainsClasses('/TestFolder/TestClass.php', array('TestFolder_TestClass'));
        $this->givenIHaveASeparatorRuleWith_AsSeparatorOnDirectory('_', '/');
        $this->whenITryToLoadExistingClass('TestFolder_TestClass');
        $this->thenIShouldHaveLoadedFile('/TestFolder/TestClass.php');
    }


    public function testRegisteredSeparatorRuleClassLoaderLoadClassTwice()
    {
        $this->testRegisteredSeparatorRuleClassLoader();
        $this->whenITryToLoadExistingClassASecondTime('TestFolder_TestClass');
        $this->thenIShouldNotHaveTriedToLoadAnything();
    }


    public function testRegisteredNamespaceRuleClassLoader()
    {
        $this->givenIHaveAClassLoader();
        $this->givenIHaveAPhpFile_ThatContainsClasses('/TestFolder/TestClass2.php', array('\TestFolder\TestClass2'));
        $this->givenIHaveANamespaceRuleOnDirectory('/');
        $this->whenITryToLoadExistingClass('\TestFolder\TestClass2');
        $this->thenIShouldHaveLoadedFile('/TestFolder/TestClass2.php');
    }
}