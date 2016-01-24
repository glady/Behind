<?php
/*
 * This file is part of the Behind-Project (https://github.com/glady/Behind).
 *
 * (c) Mike Gladysch <mail@mike-gladysch.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace glady\Behind\Diff;

/**
 * Class Comparator
 * @package glady\Behind\Diff
 */
class Comparator extends ComparatorSettings
{
    const CLASS_NAME = __CLASS__;


    public function isEqual($textA, $textB)
    {
        if ($textA === null) {
            return $textB === null;
        }
        if ($textB === null) {
            return false;
        }
        $this->modifyCompareValues($textA, $textB);
        return $this->isCaseSensitive()
            ? strcmp($textA, $textB) === 0
            : strcasecmp($textA, $textB) === 0;
    }


    public function isNotEqual($textA, $textB)
    {
        return !$this->isEqual($textA, $textB);
    }


    public function linesEqual($textA, $textB)
    {
        $modifier = new Comparator();
        $modifier->setIgnoreWhitespaces(false);
        $modifier->modifyCompareValues($textA, $textB);
        $linesA = explode("\n", $textA);
        $linesB = explode("\n", $textB);
        $lines = max(count($linesA), count($linesB));
        $result = array();
        for ($i = 0; $i < $lines; $i++) {
            $lineA = isset($linesA[$i]) ? $linesA[$i] : null;
            $lineB = isset($linesB[$i]) ? $linesB[$i] : null;
            $result[] = $this->isEqual($lineA, $lineB);
        }
        return $result;
    }


    /**
     * @param $textA
     * @param $textB
     */
    public function modifyCompareValues(&$textA, &$textB)
    {
        // standard replaces: unify line separators
        $search = array("\r\n", "\r", "\n");
        $replace = array_fill(0, 3, $this->getLineSeparator());

        // replace tab to ' ', then replace double '  ' to ' '
        if ($this->isIgnoreWhitespaces()) {
            $search[] = "\t";
            $replace[] = " ";

            $search[] = "  ";
            $replace[] = " ";
        }

        $textA = $this->replaceAll($search, $replace, $textA);
        $textB = $this->replaceAll($search, $replace, $textB);

        // remove ' '-character at start and end of string
        if ($this->isIgnoreWhitespaces()) {
            $textA = trim($textA, ' ');
            $textB = trim($textB, ' ');
        }
    }


    private function replaceAll($search, $replace, $text)
    {
        while (($replaced = str_replace($search, $replace, $text)) && $replaced !== $text) {
            $text = $replaced;
        }
        return $replaced;
    }

}
