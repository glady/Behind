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

use glady\Behind\Dependency\Configuration\DependencyConfiguration;
use glady\Behind\Dependency\Configuration\DependencyConfigurationContainer;
use glady\Behind\Dependency\DependencyException;
use glady\Behind\Dependency\Registry;

/**
 * Class BaseDependencyContainer
 * @package glady\DependencyContainerPrototype\DependencyContainer
 */
abstract class DependencyInjectionContainer extends Registry
{

    /** @var DependencyConfigurationContainer */
    private $dependencyConfigurationContainer;


    /**
     * DependencyInjectionContainer constructor.
     * @param DependencyConfigurationContainer $dependencyConfigurationContainer
     */
    public function __construct(DependencyConfigurationContainer $dependencyConfigurationContainer)
    {
        $this->dependencyConfigurationContainer = $dependencyConfigurationContainer;
    }


    /**
     * @param string $dependencyName
     * @return mixed
     */
    protected function get($dependencyName)
    {
        if (!$this->isShared($dependencyName) || !$this->isRegistered($dependencyName)) {
            $dependency = $this->createInstance($dependencyName);
            $this->register($dependencyName, $dependency);
        }
        return $this->getRegistered($dependencyName);

    }


    /**
     * @param string $dependencyName
     * @return bool
     */
    protected function isShared($dependencyName)
    {
        $dependency = $this->dependencyConfigurationContainer->getDependency($dependencyName);
        return $dependency->isShared();
    }


    /**
     * @param string $dependencyName
     * @return object
     * @throws DependencyException
     */
    protected function createInstance($dependencyName)
    {
        $dependency = $this->dependencyConfigurationContainer->getDependency($dependencyName);

        if ($dependency === null) {
            throw DependencyException::create(
                DependencyException::ERROR_NOT_CONFIGURED,
                array('dependencyName' => $dependencyName)
            );
            // better this way?
            //throw new DependencyException(
            //    "DependencyException - dependency '$dependencyName' was requested, but not configured.",
            //    DependencyException::CODE_NOT_CONFIGURED
            //);
        }

        $className = $dependency->getClass();
        $wrapperName = $dependency->getWrapper();

        $dependencyWrapper = $this->getDependencyWrapper($wrapperName);
        $instance = new $className($dependencyWrapper);
        $this->initializeInstance($instance, $dependency);

        return $instance;
    }


    /**
     * @param $wrapperName
     * @return DependencyInjectionContainer
     */
    protected function getDependencyWrapper($wrapperName)
    {
        $dependencyWrapper = $this;
        if ($wrapperName) {
            $dependencyWrapper = new $wrapperName($this);
        }
        return $dependencyWrapper;
    }


    /**
     * @param mixed                   $instance
     * @param DependencyConfiguration $dependency
     */
    protected function initializeInstance($instance, DependencyConfiguration $dependency)
    {
        $initializeCallback = $dependency->getInitializeCallback();
        if ($initializeCallback && is_callable($initializeCallback)) {
            call_user_func($initializeCallback, $instance);
        }
    }
}
