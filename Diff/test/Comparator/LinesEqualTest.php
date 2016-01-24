<?php
/*
 * This file is part of the Behind-Project (https://github.com/glady/Behind).
 *
 * (c) Mike Gladysch <mail@mike-gladysch.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace glady\Behind\Diff\test\Comparator;

use glady\Behind\Diff\Comparator;
use glady\Behind\TestFramework\UnitTest\TestCase;

/**
 * Class DiffLinesTest
 * @package glady\Behind\Diff\test\Comparator
 */
class LinesEqualTest extends TestCase
{

    public function testLinesEqualWithEmptyText()
    {
        $comparator = new Comparator();

        $this->assertComparisonLines($comparator, "", "", array(true));
    }


    public function testLinesEqualWithOneLineText()
    {
        $comparator = new Comparator();

        $this->assertComparisonLines($comparator, "a", "a", array(true));
        $this->assertComparisonLines($comparator, "a", "b", array(false));
    }


    public function testLinesEqualWithTwoLineText()
    {
        $comparator = new Comparator();

        $this->assertComparisonLines($comparator, "a\n", "a\n", array(true, true));
        $this->assertComparisonLines($comparator, "a\n", "a\n ", array(true, false)); // no ignore whitespace
        $this->assertComparisonLines($comparator, "a\n ", "a\n  ", array(true, false)); // no ignore whitespace
        $this->assertComparisonLines($comparator, "a \n", "a  ", array(false, false)); // no ignore whitespace
        $this->assertComparisonLines($comparator, "a\n", "a ", array(false, false)); // no ignore whitespace
        $this->assertComparisonLines($comparator, "a\n", "b\n", array(false, true));
        $this->assertComparisonLines($comparator, "a\n", "b", array(false, false));

        $comparator->setIgnoreWhitespaces(true);
        $this->assertComparisonLines($comparator, "a\n", "a\n ", array(true, true));
        $this->assertComparisonLines($comparator, "a\n ", "a\n  ", array(true, true));
        $this->assertComparisonLines($comparator, "a \n", "a  ", array(true, false));
        $this->assertComparisonLines($comparator, "a\n", "a ", array(true, false));
    }


    public function testLinesEqualWithTextWithSameLineCount()
    {
        $comparator = new Comparator();

        $this->assertComparisonLines($comparator, "a\nb\rc\r\nd", "a\nb\nc\nd", array(true, true, true, true));
        $this->assertComparisonLines($comparator, "!\nb\rc\r\nd", "a\nb\nc\nd", array(false, true, true, true));
        $this->assertComparisonLines($comparator, "a\n!\rc\r\nd", "a\nb\nc\nd", array(true, false, true, true));
        $this->assertComparisonLines($comparator, "a\nb\r!\r\nd", "a\nb\nc\nd", array(true, true, false, true));
        $this->assertComparisonLines($comparator, "a\nb\rc\r\n!", "a\nb\nc\nd", array(true, true, true, false));
    }


    public function testLineInserted()
    {
        $this->markTestIncomplete('not implemented yet');
        $comparator = new Comparator();

        $this->assertComparisonLines($comparator, "a\nc", "a\nb\nc", array(true, false, true));
    }


    public function testLineDeleted()
    {
        $this->markTestIncomplete('not implemented yet');
        $comparator = new Comparator();

        $this->assertComparisonLines($comparator, "a\nb\nc", "a\nc", array(true, false, true));
    }


    private function assertComparisonLines(Comparator $comparator, $a, $b, $expected)
    {
        $this->assertSame($expected, $comparator->linesEqual($a, $b));
        $this->assertSame($expected, $comparator->linesEqual($b, $a));
    }
}
