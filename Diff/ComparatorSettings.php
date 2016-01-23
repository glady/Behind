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
abstract class ComparatorSettings
{
    /** @var bool */
    private $caseSensitive = false;

    /** @var bool */
    private $ignoreWhitespaces = false;


    /**
     * @return bool
     */
    public function isCaseSensitive()
    {
        return $this->caseSensitive;
    }


    /**
     * @param bool $caseSensitive
     * @return $this
     */
    public function setCaseSensitive($caseSensitive = true)
    {
        $this->caseSensitive = (bool)$caseSensitive;
        return $this;
    }


    /**
     * @return bool
     */
    public function isIgnoreWhitespaces()
    {
        return $this->ignoreWhitespaces;
    }


    /**
     * @param bool $ignoreWhitespaces
     * @return $this
     */
    public function setIgnoreWhitespaces($ignoreWhitespaces = true)
    {
        $this->ignoreWhitespaces = (bool)$ignoreWhitespaces;
        return $this;
    }

}
