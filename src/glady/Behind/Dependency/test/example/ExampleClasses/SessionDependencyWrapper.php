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

use glady\Behind\Dependency\Injection\DependencyInjectionContainer;
use glady\Behind\Dependency\Injection\PublicDependencyInjectionContainer;

/**
 * Class SessionDependencyWrapper
 * @package glady\DependencyContainerPrototype\Example
 */
class SessionDependencyWrapper implements SessionDependencyInterface
{
    const CLASS_NAME = __CLASS__;

    /** @var PublicDependencyInjectionContainer */
    private $dependencyContainer;


    public function __construct(DependencyInjectionContainer $dependencyContainer)
    {
        $this->dependencyContainer = $dependencyContainer;
    }


    public function getSomeRequestParam()
    {
        $request = $this->dependencyContainer->get('request');
        return $request->getRequestParam('someRequestParam');
    }
}
