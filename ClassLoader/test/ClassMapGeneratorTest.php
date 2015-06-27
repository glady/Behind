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
use glady\Behind\Utils\File\File;
use glady\Behind\Utils\File\Iterator;

/**
 * Class ClassMapGeneratorTest
 * @package glady\Behind\ClassLoader\test
 */
class ClassMapGeneratorTest extends TestCase
{
    protected $className = '\glady\Behind\ClassLoader\ClassMapGenerator';


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
        if (defined('T_TRAIT')) {
            //$this->addFileToPath($fixturePath, 'Trait.php', $this->buildTraitCode('MyTrait'));
        }
        $this->addFileToPath($fixturePath . '/subfolder', 'X.php', $this->buildAbstractClassCode('X'));
        $this->addFileToPath($fixturePath . '/subfolder', 'AA.php', $this->buildInterfaceCode('AA'));

        $classMapGenerator = new ClassMapGenerator();
        $classMapGenerator->addPath($fixturePath);

        $expected = array(
            'A' => realpath($fixturePath . '/A.php'),
            'AA' => realpath($fixturePath . '/subfolder/AA.php'),
            'B' => realpath($fixturePath . '/B.php'),
            'C' => realpath($fixturePath . '/X.php'),
            //'MyTrait' => realpath($fixturePath . '/Trait.php'),
            'X' => realpath($fixturePath . '/subfolder/X.php'),
        );
        if (!defined('T_TRAIT')) {
            unset($expected['MyTrait']);
        }
        $this->assertEquals($expected, $classMapGenerator->generate());
    }


    public function testMultipleClasses()
    {
        $fixturePath = $this->getPreparedFixturePath();

        $classMapGenerator = new ClassMapGenerator();
        $classMapGenerator->addPath($fixturePath);

        // with default settings H is not found!
        $this->addFileToPath($fixturePath, 'GsndH.php', $this->buildClassCode('G') . "\n" . $this->buildClassCode('H'));
        $this->assertEquals(array('G' => realpath($fixturePath . '/GsndH.php')), $classMapGenerator->generate());;
        // with default settings G is not found!
        $this->addFileToPath($fixturePath, 'GsndH.php', $this->buildClassCode('H') . "\n" . $this->buildClassCode('G'));
        $this->assertEquals(array('H' => realpath($fixturePath . '/GsndH.php')), $classMapGenerator->generate());;

        // activate full processing of all tokens
        $classMapGenerator->acceptMultipleClassesPerFile(true);
        $this->assertEquals(array(
            'G' => realpath($fixturePath . '/GsndH.php'),
            'H' => realpath($fixturePath . '/GsndH.php'),
        ), $classMapGenerator->generate());;

    }


    private function cleanUpPathRecursive($path)
    {
        $cleanup = array();
        $files = new Iterator($path);

        $files->forEachFile(function(File $fileOrDir) use (&$cleanup) {
            $realPath = $fileOrDir->getRealPath();
            $cleanup[] = $realPath;
        });
        foreach ($cleanup as $realPath) {
            unlink($realPath);
        }
    }


    private function addFileToPath($path, $filename, $code)
    {
        if (!is_dir(&$path)) {
            mkdir(&$path, 0777, true);
        }
        file_put_contents("$path/$filename", $code);
    }


    /**
     * @param string $className
     * @return string
     */
    private function buildClassCode($className)
    {
        return "<?php\nclass $className\n{}";
    }

    /**
     * @param string $className
     * @return string
     */
    private function buildAbstractClassCode($className)
    {
        return "<?php\nabstract class $className\n{}";
    }

    /**
     * @param string $className
     * @return string
     */
    private function buildInterfaceCode($className)
    {
        return "<?php\ninterface $className\n{}";
    }

    /**
     * @param string $className
     * @return string
     */
    private function buildTraitCode($className)
    {
        return "<?php\trait $className\n{}";
    }


    /**
     * @return string
     */
    private function getPreparedFixturePath()
    {
        $fixturePath = __DIR__ . '/fixture';
        if (is_dir($fixturePath)) {
            $this->cleanUpPathRecursive($fixturePath);
        }
        else {
            mkdir($fixturePath, 0777, true);
        }
        return $fixturePath;
    }
}