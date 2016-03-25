<?php
/*
 * This file is part of the Behind-Project (https://github.com/glady/Behind).
 *
 * (c) Mike Gladysch <mail@mike-gladysch.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace glady\Behind\TestFramework\UnitTest;

/**
 * Class TestCase
 * @package glady\Behind\TestFramework\UnitTest
 */
abstract class BaseClassTestCase extends TestCase
{
    /** @var string */
    protected $className = null;


    /**
     *
     */
    public function testClassIsDefined()
    {
        if ($this->className) {
            $this->assertTrue(class_exists($this->className));
        }
        else {
            $this->markTestSkipped('no className given');
        }
    }
}
