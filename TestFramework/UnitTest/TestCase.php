<?php

namespace glady\Behind\TestFramework\UnitTest;



abstract class TestCase extends \PHPUnit_Framework_TestCase
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
    }
} 