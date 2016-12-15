<?php
/*
 * This file is part of the Behind-Project (https://github.com/glady/Behind).
 *
 * (c) Mike Gladysch <mail@mike-gladysch.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace glady\Behind\Dependency\Injection;

/**
 * Class DependencyContainer
 * @package glady\DependencyContainerPrototype
 */
class PublicDependencyInjectionContainer extends DependencyInjectionContainer
{
    public function get($dependencyName)
    {
        return parent::get($dependencyName);
    }
}
