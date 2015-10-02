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

use glady\Behind\ClassLoader\ClassMapGenerator;
use glady\Behind\TestFramework\UnitTest\TestCase;
use glady\Behind\Utils\File\Directory;
use glady\Behind\Utils\File\File;
use glady\Behind\Utils\File\Iterator;

/**
 * Class ClassMapGeneratorTest
 * @package glady\Behind\ClassLoader\test
 */
class ClassMapGeneratorTest extends TestCase
{
    protected $className = '\glady\Behind\ClassLoader\ClassMapGenerator';


    public static function tearDownAfterClass()
    {
        $fixturePath = __DIR__ . '/fixture';
        if (is_dir($fixturePath)) {
            self::cleanUpPathRecursive($fixturePath);
        }
        parent::tearDownAfterClass();
    }


    public function testEmpty()
    {
        $classMapGenerator = new ClassMapGenerator();
        $this->assertEquals(array(), $classMapGenerator->generate());
    }


    public function testBehindClassLoaderFolder()
    {
        $fixturePath = $this->getPreparedFixturePath();
        $this->addFileToPath($fixturePath, 'A.php', $this->buildClassCode('A'));
        $this->addFileToPath($fixturePath, 'B.php', $this->buildClassCode('B'));
        $this->addFileToPath($fixturePath, 'X.php', $this->buildClassCode('C'));
        $this->addFileToPath($fixturePath, 'NamespacedA.php', $this->buildClassCode('A', 'MyNamespace'));
        $this->addFileToPath($fixturePath, 'Trait.php', $this->buildTraitCode('MyTrait'));
        $this->addFileToPath($fixturePath . '/subfolder', 'X.php', $this->buildAbstractClassCode('X'));
        $this->addFileToPath($fixturePath . '/subfolder', 'AA.php', $this->buildInterfaceCode('AA'));

        $classMapGenerator = new ClassMapGenerator();
        $classMapGenerator->setRelativeToPath($fixturePath);
        $classMapGenerator->addPath($fixturePath);

        $expected = array(
            'A' => 'A.php',
            'AA' => 'subfolder/AA.php',
            'B' => 'B.php',
            'C' => 'X.php',
            'MyNamespace\A' => 'NamespacedA.php',
            'MyTrait' => 'Trait.php',
            'X' => 'subfolder/X.php',
        );

        // trait file exists but class map does not recognize it because tokenizer does not know anything about traits
        if (version_compare(PHP_VERSION, '5.4', '<')) {
            unset($expected['MyTrait']);
        }
        $this->assertEquals($expected, $classMapGenerator->generate());
    }


    public function testMultipleClasses()
    {
        $fixturePath = $this->getPreparedFixturePath();

        $classMapGenerator = new ClassMapGenerator();
        $classMapGenerator->setRelativeToPath($fixturePath);
        $classMapGenerator->addPath($fixturePath);

        // with default settings H is not found!
        $this->addFileToPath($fixturePath, 'GsndH.php', $this->buildClassCode('G') . "\n?>\n" . $this->buildClassCode('H'));
        $this->assertEquals(array('G' => 'GsndH.php'), $classMapGenerator->generate());;

        // with default settings G is not found!
        $this->addFileToPath($fixturePath, 'GsndH.php', $this->buildClassCode('H') . "\n?>\n" . $this->buildClassCode('G'));
        $this->assertEquals(array('H' => 'GsndH.php'), $classMapGenerator->generate());;

        // activate full processing of all tokens
        $classMapGenerator->acceptMultipleClassesPerFile(true);
        $this->assertEquals(array(
            'G' => 'GsndH.php',
            'H' => 'GsndH.php',
        ), $classMapGenerator->generate());;

    }


    private static function cleanUpPathRecursive($path)
    {
        $cleanup = array();
        $files = new Iterator($path);

        $files->forEachFile(function(File $file) use (&$cleanup) {
            $realPath = $file->getRealPath();
            $cleanup[] = $realPath;
        });
        foreach ($cleanup as $realPath) {
            unlink($realPath);
        }

        $cleanup = array();
        $files->forEachDirectory(function(Directory $dir) use (&$cleanup) {
            $realPath = $dir->getRealPath();
            $cleanup[] = $realPath;
        });
        foreach ($cleanup as $realPath) {
            rmdir($realPath);
        }

        rmdir($path);
    }


    private function addFileToPath($path, $filename, $code)
    {
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }
        file_put_contents("$path/$filename", $code);
    }


    /**
     * @param string $className
     * @param string $namespace
     * @param string $obejct
     * @return string
     */
    private function buildClassCode($className, $namespace = '', $obejct = 'class')
    {
        $opening = $this->getOpeningCode($namespace);
        return "$opening\n$obejct $className\n{}";
    }


    /**
     * @param string $className
     * @param string $namespace
     * @return string
     */
    private function buildAbstractClassCode($className, $namespace = '')
    {
        return $this->buildClassCode($className, $namespace, 'abstract class');
    }


    /**
     * @param string $className
     * @param string $namespace
     * @return string
     */
    private function buildInterfaceCode($className, $namespace = '')
    {
        return $this->buildClassCode($className, $namespace, 'interface');
    }


    /**
     * @param string $className
     * @param string $namespace
     * @return string
     */
    private function buildTraitCode($className, $namespace = '')
    {
        return $this->buildClassCode($className, $namespace, 'trait');
    }


    /**
     * @return string
     */
    private function getPreparedFixturePath()
    {
        $fixturePath = __DIR__ . '/fixture';
        if (is_dir($fixturePath)) {
            self::cleanUpPathRecursive($fixturePath);
        }
        else {
            mkdir($fixturePath, 0777, true);
        }
        return $fixturePath;
    }


    /**
     * @param string $namespace
     * @return string
     */
    private function getOpeningCode($namespace = '')
    {
        return "<?php"
            . ($namespace ? "\nnamespace $namespace;" : '');
    }


    public function testParsingClassToken()
    {
        $php = "<?php\nclass X {\nfunction test() { return self::class; }\n}";

        $fixturePath = $this->getPreparedFixturePath();

        $classMapGenerator = new ClassMapGenerator();
        $classMapGenerator->setRelativeToPath($fixturePath);
        $classMapGenerator->addPath($fixturePath);

        $this->addFileToPath($fixturePath, 'X.php', $php);

        $this->assertEquals(array('X' => 'X.php'), $classMapGenerator->generate());;
    }


    public function testParsingClassTokenWithMultipleClassesAllowed()
    {
        $php = "<?php\nclass X {\nfunction test() { return self::class; }\n}";

        $fixturePath = $this->getPreparedFixturePath();

        $classMapGenerator = new ClassMapGenerator();
        $classMapGenerator->acceptMultipleClassesPerFile();
        $classMapGenerator->setRelativeToPath($fixturePath);
        $classMapGenerator->addPath($fixturePath);

        $this->addFileToPath($fixturePath, 'X.php', $php);

        $this->assertEquals(array('X' => 'X.php'), $classMapGenerator->generate());;
    }



    public function testParsingClassTokenWithMultipleClassesAllowedAndSecondClass()
    {
        $php = "<?php\nclass X {\nfunction test() { return self::class; }\n}\n\nclass Y [}";

        $fixturePath = $this->getPreparedFixturePath();

        $classMapGenerator = new ClassMapGenerator();
        $classMapGenerator->acceptMultipleClassesPerFile();
        $classMapGenerator->setRelativeToPath($fixturePath);
        $classMapGenerator->addPath($fixturePath);

        $this->addFileToPath($fixturePath, 'X.php', $php);

        $this->assertEquals(array(
            'X' => 'X.php',
            'Y' => 'X.php'
        ), $classMapGenerator->generate());
    }
}
