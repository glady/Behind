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


    public function testRegisteredSeparatorWithSubDirMappingRuleClassLoader()
    {
        $this->givenIHaveAClassLoader();
        $this->givenIHaveAPhpFile_ThatContainsClasses('/TestFolder/TestClass/_TestClass.php', array('TestFolder_TestClass'));
        $this->givenIHaveASeparatorRuleWith_AndSubDirMappingCharacter_AsSeparatorOnDirectory('_', '_', '/');
        $this->whenITryToLoadExistingClass('TestFolder_TestClass');
        $this->thenIShouldHaveLoadedFile('/TestFolder/TestClass/_TestClass.php');
    }


    public function testRegisteredSeparatorWithFixedNamespaceRuleClassLoader()
    {
        $this->givenIHaveAClassLoader();
        $this->givenIHaveAPhpFile_ThatContainsClasses('/somewhere/myNamespace/TestFolder/TestClass.php', array('SomeLibrary_TestFolder_TestClass'));
        $this->givenIHaveASeparatorRuleWith_AndFixedNamespace_OnDirectory_AsSeparatorOnDirectory('_', 'SomeLibrary', '/somewhere/myNamespace', '/');
        $this->whenITryToLoadExistingClass('SomeLibrary_TestFolder_TestClass');
        $this->thenIShouldHaveLoadedFile('/somewhere/myNamespace/TestFolder/TestClass.php');
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


    public function testNotMatchingRulesAndAMatchingFirstRuleClassLoader()
    {
        $this->givenIHaveAClassLoader();
        $this->givenIHaveAPhpFile_ThatContainsClasses('/TestFolder/TestClass.php', array('TestFolder_TestClass'));
        $this->givenIHaveAPhpFile_ThatContainsClasses('/TestFolder/TestClass/_TestClass.php', array('\TestFolder\TestClass'));
        $this->givenIHaveAPhpFile_ThatContainsClasses('/TestFolder/TestClass/__TestClass.php', array('SomeOtherClass'));
        $this->givenIHaveASeparatorRuleWith_AsSeparatorOnDirectory('_', '/'); // should match
        $this->givenIHaveASeparatorRuleWith_AndSubDirMappingCharacter_AsSeparatorOnDirectory('_', '__', '/'); // should not match
        $this->givenIHaveASeparatorRuleWith_AndSubDirMappingCharacter_AsSeparatorOnDirectory('_', '_', '/'); // should not match
        $this->whenITryToLoadExistingClass('TestFolder_TestClass');
        $this->thenIShouldHaveLoadedFile('/TestFolder/TestClass.php');
    }


    public function testNotMatchingRulesAndAMatchingMiddleRuleClassLoader()
    {
        $this->givenIHaveAClassLoader();
        $this->givenIHaveAPhpFile_ThatContainsClasses('/TestFolder/TestClass.php', array('TestFolder_TestClass'));
        $this->givenIHaveAPhpFile_ThatContainsClasses('/TestFolder/TestClass/_TestClass.php', array('\TestFolder\TestClass'));
        $this->givenIHaveAPhpFile_ThatContainsClasses('/TestFolder/TestClass/__TestClass.php', array('SomeOtherClass'));
        $this->givenIHaveASeparatorRuleWith_AndSubDirMappingCharacter_AsSeparatorOnDirectory('_', '__', '/'); // should not match
        $this->givenIHaveASeparatorRuleWith_AsSeparatorOnDirectory('_', '/'); // should match
        $this->givenIHaveASeparatorRuleWith_AndSubDirMappingCharacter_AsSeparatorOnDirectory('_', '_', '/'); // should not match
        $this->whenITryToLoadExistingClass('TestFolder_TestClass');
        $this->thenIShouldHaveLoadedFile('/TestFolder/TestClass.php');
    }


    public function testNotMatchingRulesAndAMatchingLastRuleClassLoader()
    {
        $this->givenIHaveAClassLoader();
        $this->givenIHaveAPhpFile_ThatContainsClasses('/TestFolder/TestClass.php', array('TestFolder_TestClass'));
        $this->givenIHaveAPhpFile_ThatContainsClasses('/TestFolder/TestClass/_TestClass.php', array('\TestFolder\TestClass'));
        $this->givenIHaveAPhpFile_ThatContainsClasses('/TestFolder/TestClass/__TestClass.php', array('SomeOtherClass'));
        $this->givenIHaveASeparatorRuleWith_AndSubDirMappingCharacter_AsSeparatorOnDirectory('_', '__', '/'); // should not match
        $this->givenIHaveASeparatorRuleWith_AndSubDirMappingCharacter_AsSeparatorOnDirectory('_', '_', '/'); // should not match
        $this->givenIHaveASeparatorRuleWith_AsSeparatorOnDirectory('_', '/'); // should match
        $this->whenITryToLoadExistingClass('TestFolder_TestClass');
        $this->thenIShouldHaveLoadedFile('/TestFolder/TestClass.php');
    }
}