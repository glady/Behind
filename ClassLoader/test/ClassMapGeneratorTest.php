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
        $fixturePath = __DIR__ . '/fixture';

        $this->cleanUpPathRecursive($fixturePath);
        $this->addFileToPath($fixturePath, 'A.php', $this->buildClassCode('A'));
        $this->addFileToPath($fixturePath, 'B.php', $this->buildClassCode('B'));
        $this->addFileToPath($fixturePath, 'X.php', $this->buildClassCode('C'));

        $classMapGenerator = new ClassMapGenerator();
        $classMapGenerator->addPath($fixturePath);

        $this->assertEquals(array(
            'A' => realpath($fixturePath . '/A.php'),
            'B' => realpath($fixturePath . '/B.php'),
            'C' => realpath($fixturePath . '/X.php'),
        ), $classMapGenerator->generate());

    }


    private function cleanUpPathRecursive($path)
    {
        $cleanup = array();
        $files = new Iterator($path);

        $files->forEachFile(function(File $fileOrDir) use ($cleanup) {
            $realPath = $fileOrDir->getRealPath();
            $cleanup[] = $realPath;
        });
        foreach ($cleanup as $realPath) {
            unlink($realPath);
        }
    }


    private function addFileToPath($path, $filename, $code)
    {
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
}
