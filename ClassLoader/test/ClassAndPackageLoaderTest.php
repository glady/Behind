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

require_once __DIR__ . '/../ClassAndPackageLoader.php';
require_once __DIR__ . '/../../TestFramework/UnitTest/TestCase.php';

use glady\Behind\ClassLoader\ClassAndPackageLoader;
use glady\Behind\TestFramework\UnitTest\TestCase;

/**
 * @author  gladysch
 * @created 25.03.2014
 */
class ClassAndPackageLoaderTest extends TestCase
{

    const FILENAME = '/testfolder/test.php';
    const CLASSNAME = 'TestClass';
    const TESTNAMESPACE = 'TestNamespace';


    public function testClassExists()
    {
        $this->assertTrue(class_exists('\glady\Behind\ClassLoader\ClassAndPackageLoader', false));
    }


    /**
     * @dataProvider provideNormalizeNamespaceForPackage
     * @param $codeBefore
     * @param $expectedPackageCode
     */
    public function testNormalizeNamespaceForPackage($codeBefore, $expectedPackageCode)
    {
        // force array of lines
        if (is_string($codeBefore)) {
            $codeBefore = explode("\n", $codeBefore);
        }

        $loader = new ClassAndPackageLoader();

        $actualPackageCode = $loader->normalizeNamespaceForPackage($codeBefore, self::CLASSNAME, self::FILENAME);

        $this->assertEquals($expectedPackageCode, $actualPackageCode);
    }


    /**
     * @return array
     */
    public function provideNormalizeNamespaceForPackage()
    {
        $fileName = self::FILENAME;
        $className = self::CLASSNAME;
        $namespace = self::TESTNAMESPACE;

        // attention: we will not check, that $className is valid!
        $classCheck = "if (!class_exists('$className', false)) {";

        $class1 = "class $className\n{\n    // some code\n}";
        $use1 = "use glady\\Behind;";

        $testCases = array();

        $testCases[] = array(
            $class1,
            "//start of file: '$fileName'\n"
            . "namespace {\n"
            . "$classCheck\n"
            . "$class1\n"
            . "}\n}\n"
            . "//end of file: '$fileName'\n"
        );

        $class2 = "namespace $namespace;\n$class1";
        $testCases[] = array(
            $class2,
            "//start of file: '$fileName'\n"
            . "namespace $namespace{\n"
            . "$classCheck\n"
            . "$class1\n"
            . "}\n}\n"
            . "//end of file: '$fileName'\n"
        );


        $class3 = "namespace $namespace;\n$use1\n$class1";
        // class4 = class3 with {} notation...
        $class4 = "namespace $namespace{\n$use1\n$class1\n}";
        // so they have same expected package
        $expectedPackageForClass3AndClass4 = (
            "//start of file: '$fileName'\n"
            . "namespace $namespace{\n"
            . "$use1\n"
            . "$classCheck\n"
            . "$class1\n"
            . "}\n}\n"
            . "//end of file: '$fileName'\n"
        );

        $testCases[] = array($class3, $expectedPackageForClass3AndClass4);
        $testCases[] = array($class4, $expectedPackageForClass3AndClass4);

        return $testCases;
    }

}