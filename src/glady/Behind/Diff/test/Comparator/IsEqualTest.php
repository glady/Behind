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
 * Class IsEqualTest
 * @package glady\Behind\Diff\test\Comparator
 */
class IsEqualTest extends TestCase
{

    public function testEmptyStringsAreEqual()
    {
        $comparator = new Comparator();

        $this->assertComparisonEqual($comparator, '', '');

        $comparator->setCaseSensitive(true);
        $this->assertComparisonEqual($comparator, '', '');
    }


    public function testNullValues()
    {
        $comparator = new Comparator();

        $this->assertComparisonNotEqual($comparator, '', null);
        $this->assertComparisonNotEqual($comparator, null, '');
        $this->assertComparisonEqual($comparator, null, null);
    }


    public function testCaseSensitive()
    {
        $comparator = new Comparator();

        $this->assertComparisonEqual($comparator, 'a', 'a');
        $this->assertComparisonEqual($comparator, 'a', 'A');
        $this->assertComparisonNotEqual($comparator, 'a', 'b');
        $this->assertComparisonNotEqual($comparator, 'a', 'B');
        $this->assertComparisonEqual($comparator, 'Hello World!', 'heLlo woRld!');

        $comparator->setCaseSensitive(true);

        $this->assertComparisonEqual($comparator, 'a', 'a');
        $this->assertComparisonNotEqual($comparator, 'a', 'A');
        $this->assertComparisonNotEqual($comparator, 'a', 'b');
        $this->assertComparisonNotEqual($comparator, 'a', 'B');
        $this->assertComparisonNotEqual($comparator, 'Hello World!', 'heLlo woRld!');
        $this->assertComparisonEqual($comparator, 'Hello World!', 'Hello World!');
    }


    public function testIgnoreWhiteSpaces()
    {
        $comparator = new Comparator();

        $this->assertComparisonNotEqual($comparator, 'a b', 'a     b');
        $this->assertComparisonNotEqual($comparator, 'a b', "a  \n   b");
        $this->assertComparisonNotEqual($comparator, "a\tb", "a\t b");
        $this->assertComparisonNotEqual($comparator, "a\tb", "a \tb");
        $this->assertComparisonNotEqual($comparator, "a\tb", "a b");
        $this->assertComparisonNotEqual($comparator, "a\tb", "ab");

        $comparator->setIgnoreWhitespaces(true);

        $this->assertComparisonEqual($comparator, 'a b', 'a     b');
        $this->assertComparisonNotEqual($comparator, 'a b', "a  \n   b");
        $this->assertComparisonEqual($comparator, "a \n b", "a  \n   b");
        $this->assertComparisonEqual($comparator, "a\tb", "a\t b");
        $this->assertComparisonEqual($comparator, "a\tb", "a \tb");
        $this->assertComparisonEqual($comparator, "a\tb", "a b");
        $this->assertComparisonNotEqual($comparator, "a\tb", "ab");
    }


    public function testLineBreaks()
    {
        $comparator = new Comparator();

        $this->assertComparisonEqual($comparator, "a \r\n b", "a \n b");
        $this->assertComparisonEqual($comparator, "a \r b", "a \n b");
        $this->assertComparisonEqual($comparator, "a \n b", "a \n b");
    }


    /**
     * @param Comparator $comparator
     * @param string     $a
     * @param string     $b
     */
    private function assertComparisonEqual(Comparator $comparator, $a, $b)
    {
        $message = "assertComparisonEqual: " . json_encode(array($a, $b));

        $this->assertTrue($comparator->isEqual($a, $b), $message);
        $this->assertFalse($comparator->isNotEqual($a, $b), $message);
        // same result when change $a and $b
        $this->assertTrue($comparator->isEqual($b, $a), $message);
        $this->assertFalse($comparator->isNotEqual($b, $a), $message);
    }


    /**
     * @param Comparator $comparator
     * @param string     $a
     * @param string     $b
     */
    private function assertComparisonNotEqual(Comparator $comparator, $a, $b)
    {
        $message = "assertComparisonNotEqual: " . json_encode(array($a, $b));

        $this->assertFalse($comparator->isEqual($a, $b), $message);
        $this->assertTrue($comparator->isNotEqual($a, $b), $message);
        // same result when change $a and $b
        $this->assertFalse($comparator->isEqual($b, $a), $message);
        $this->assertTrue($comparator->isNotEqual($b, $a), $message);

    }
}
