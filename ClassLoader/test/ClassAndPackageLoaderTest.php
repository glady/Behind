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
    protected $className = '\glady\Behind\ClassLoader\ClassAndPackageLoader';

    const FILENAME = '/testfolder/test.php';
    const CLASSNAME = 'TestClass';
    const TESTNAMESPACE = 'TestNamespace';


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

        // duplicate all cases with <?php line
        $ln = count($testCases);
        for ($i = 0; $i < $ln; $i++) {
            $testCase = $testCases[$i];
            $testCase[0] = "<?php\n" . $testCase[0];
            $testCases[] = $testCase;
        }

        return $testCases;
    }


    public function testPackagingOnInvalidFilesThrowsNoErrors()
    {
        $e = null;
        try {
            $packageLoader = $this->getMockedPackageLoader();
            $packageLoader->ignorePackageHandlingForClassesStartingWith('\\glady\\Behind');
            $packageLoader->startPackage('myPackage');
            $packageLoader->loadClass('\\glady\\Behind\\SomeClass');
            $packageLoader->loadClass('\\glady\\Behind\\ClassLoader\\ClassLoader');
            $packageLoader->stopPackage('myPackage');
            // TODO: find something for asserting / make asserting possible - some tests with valid data needed?
        }
        catch (\Exception $e) {
            // assert below
        }
        $this->assertNull($e);
    }


    /**
     * @return ClassAndPackageLoader
     */
    protected function getMockedPackageLoader()
    {
        /** @var ClassAndPackageLoader $packageLoader */
        $packageLoader = $this->getMock($this->className, array('writeToFile'));
        $packageLoader
            ->expects($this->any())
            ->method('writeToFile')->will($this->returnValue(null));

        return $packageLoader;
    }
}
