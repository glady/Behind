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
abstract class TestCase extends \PHPUnit_Framework_TestCase
{
    /** @var array */
    protected $tearDownSteps = array();


    /**
     * @param callable $callable
     * @param array    $args
     */
    protected function onTearDown($callable, array $args = array())
    {
        $this->tearDownSteps[] = array('callable' => $callable, 'args' => $args);
    }


    protected function tearDown()
    {
        foreach ($this->tearDownSteps as $step) {
            call_user_func_array($step['callable'], $step['args']);
        }
        parent::tearDown();
    }
}
