<?php
/*
 * This file is part of the Behind-Project (https://github.com/glady/Behind).
 *
 * (c) Mike Gladysch <mail@mike-gladysch.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace glady\Behind\Dependency\test\example\ExampleClasses;

/**
 * Interface SessionDependencyInterface
 * @package glady\Behind\Dependency\test\example\ExampleClasses
 */
interface SessionDependencyInterface
{

    /**
     * @return string
     */
    public function getSomeRequestParam();

}
