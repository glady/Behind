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


    public function testRegisteredClassMapRuleClassLoader()
    {
        $this->givenIHaveAClassLoader();
        $this->givenIHaveAPhpFile_ThatContainsClasses('/somewhere/else.php', array('MyClass'));
        $this->givenIHaveAClassMap_RuleOnDirectory(array('MyClass' => 'else.php'), '/somewhere');
        $this->whenITryToLoadExistingClass('MyClass');
        $this->thenIShouldHaveLoadedFile('/somewhere/else.php');
    }


    public function testRegisteredCallbackRuleClassLoaderDoesNotLoadClass()
    {
        $this->givenIHaveAClassLoader();
        $this->givenIHaveAPhpFile_ThatContainsClasses('/somewhere/else.php', array('MyClass'));
        $this->givenIHaveACallback_Rule(function () {
            return '/some/invalid/file.php';
        });
        $this->thenEvent_OccursWithFile(ClassLoader::ON_RULE_DOES_NOT_MATCH, '/some/invalid/file.php');
        $this->whenITryToLoadExistingClass('MyClass');

    }


    public function testRegisteredCallbackRuleClassLoaderDoesReturnFile()
    {
        $me = $this;
        $this->givenIHaveAClassLoader();
        $this->givenIHaveAPhpFile_ThatContainsClasses('/somewhere/else.php', array('MyClass'));
        $this->givenIHaveACallback_Rule(function () use ($me) {
            return $me->makePathOsDependentValid('/somewhere/else.php');
        });
        $this->whenITryToLoadExistingClass('MyClass');
        $this->thenIShouldHaveLoadedFile('/somewhere/else.php');
    }


    public function testRegisteredCallbackRuleClassLoaderDoesLoadClass()
    {
        $me = $this;
        $this->givenIHaveAClassLoader();
        $this->givenIHaveAPhpFile_ThatContainsClasses('/somewhere/else.php', array('MyClass'));
        $this->givenIHaveACallback_Rule(function () use ($me) {
            $me->givenClass_IsLoaded('MyClass');
            return null;
        });
        $this->whenITryToLoadExistingClass('MyClass');
        $this->thenIShouldNotHaveTriedToLoadAnythingButClassIsLoaded();
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
        $this->givenIHaveAPhpFile_ThatContainsClasses('/TestFolder/TestClass2.php', array('TestFolder\TestClass2'));
        $this->givenIHaveANamespaceRuleOnDirectory('/');
        $this->whenITryToLoadExistingClass('TestFolder\TestClass2');
        $this->thenIShouldHaveLoadedFile('/TestFolder/TestClass2.php');
    }


    public function testNotMatchingRulesAndAMatchingFirstRuleClassLoader()
    {
        $this->givenIHaveAClassLoader();
        $this->givenIHaveAPhpFile_ThatContainsClasses('/TestFolder/TestClass.php', array('TestFolder_TestClass'));
        $this->givenIHaveAPhpFile_ThatContainsClasses('/TestFolder/TestClass/_TestClass.php', array('TestFolder\TestClass'));
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
        $this->givenIHaveAPhpFile_ThatContainsClasses('/TestFolder/TestClass/_TestClass.php', array('TestFolder\TestClass'));
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
        $this->givenIHaveAPhpFile_ThatContainsClasses('/TestFolder/TestClass/_TestClass.php', array('TestFolder\TestClass'));
        $this->givenIHaveAPhpFile_ThatContainsClasses('/TestFolder/TestClass/__TestClass.php', array('SomeOtherClass'));
        $this->givenIHaveASeparatorRuleWith_AndSubDirMappingCharacter_AsSeparatorOnDirectory('_', '__', '/'); // should not match
        $this->givenIHaveASeparatorRuleWith_AndSubDirMappingCharacter_AsSeparatorOnDirectory('_', '_', '/'); // should not match
        $this->givenIHaveASeparatorRuleWith_AsSeparatorOnDirectory('_', '/'); // should match
        $this->whenITryToLoadExistingClass('TestFolder_TestClass');
        $this->thenIShouldHaveLoadedFile('/TestFolder/TestClass.php');
    }


    public function testLoadRootCLassMappedWithSubDirMapping()
    {
        $this->givenIHaveAClassLoader();
        $this->givenIHaveAPhpFile_ThatContainsClasses('/glady/Behind/_Behind.php', array('glady\Behind'));
        $this->givenIHaveASeparatorRuleWith_AndSubDirMappingCharacter_AsSeparatorOnDirectory('\\', '_', '/');
        $this->whenITryToLoadExistingClass('glady\Behind');
        $this->thenIShouldHaveLoadedFile('/glady/Behind/_Behind.php');
    }


    public function testLoadRootClassOfFixedNamespaceWithSubDirMapping()
    {
        $this->givenIHaveAClassLoader();
        $this->givenIHaveAPhpFile_ThatContainsClasses('/_Behind.php', array('glady\Behind'));
        $this->givenIHaveASeparatorRuleWith_AndSubDirMappingCharacter_AsSeparatorOnDirectory_AndWithFixedNamespaceDefinition(
            '\\', '_', '/', array('glady\Behind' => '\\')
        );
        $this->whenITryToLoadExistingClass('glady\Behind');
        $this->thenIShouldHaveLoadedFile('/_Behind.php');
    }


    public function testConflictingNamespaceFixturesDoesNotPreventLoadRightFile()
    {
        $fixedNamespaces = array(
            'glady\Behind' => '/lib',
            'glady\BehindTest' => '/test'
        );

        $this->givenIHaveAClassLoader();
        $this->givenIHaveAPhpFile_ThatContainsClasses('/test/MyLittleTest/_MyLittleTest.php', array('glady\BehindTest\MyLittleTest'));
        $this->givenIHaveASeparatorRuleWith_AndSubDirMappingCharacter_AsSeparatorOnDirectory_AndWithFixedNamespaceDefinition(
            '\\', '_', '/', $fixedNamespaces
        );
        $this->whenITryToLoadExistingClass('glady\BehindTest\MyLittleTest');
        $this->thenIShouldHaveLoadedFile('/test/MyLittleTest/_MyLittleTest.php');
    }


    public function testWrongClassName()
    {
        $this->givenIHaveAClassLoader();
        $this->givenIHaveAPhpFile_ThatContainsClasses('/TestFolder/TestClass.php', array('TestFolder_TestClassInvalid'));
        $this->givenIHaveASeparatorRuleWith_AsSeparatorOnDirectory('_', '/');

        $this->whenICheckClassExists('TestFolder_TestClass');
        $this->thenClassShouldNotBeDeclared('TestFolder_TestClass');
        $this->thenClassShouldBeDeclared('TestFolder_TestClassInvalid');

        $this->whenICheckClassExists('TestFolder_TestClass');
        $this->theNoFatalErrorShouldOccur();
    }


    public function testWrongClassNameAddRuleAndLoadAgain()
    {
        $this->testWrongClassName();

        $this->givenIHaveAPhpFile_ThatContainsClasses('/TestFolder/TestClass/_TestClass.php', array('TestFolder_TestClass'));
        $this->givenIHaveASeparatorRuleWith_AndSubDirMappingCharacter_AsSeparatorOnDirectory('_', '_', '/');

        $this->whenICheckClassExists('TestFolder_TestClass');
        $this->thenClassShouldBeDeclared('TestFolder_TestClass');
        $this->thenClassShouldBeDeclared('TestFolder_TestClassInvalid');
    }


    public function testIssue5()
    {
        $this->givenIHaveAClassLoader();
        $this->givenIHaveAPhpFile_ThatContainsClasses('/OldVersion/Framework/Package/Feature.php', array('Framework_Package_Feature'));
        $this->givenIHaveAPhpFile_ThatContainsClasses('/NewVersion/Framework/Package/Feature.php', array('Framework\\Package\\Feature'));

        $this->givenIHaveASeparatorRuleWith_AndFixedNamespace_OnDirectory_AsSeparatorOnDirectory('_', 'Framework', '/OldVersion/Framework', null);
        $this->givenIHaveASeparatorRuleWith_AndFixedNamespace_OnDirectory_AsSeparatorOnDirectory('\\', 'Framework', '/NewVersion/Framework', null);

        $this->whenITryToLoadExistingClass('Framework_Package_Feature');
        $this->whenITryToLoadExistingClass('Framework\\Package\\Feature');

        $this->thenNoFileIsIncludedTwice();
    }
}
