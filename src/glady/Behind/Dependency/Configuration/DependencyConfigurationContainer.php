<?php
/*
 * This file is part of the Behind-Project (https://github.com/glady/Behind).
 *
 * (c) Mike Gladysch <mail@mike-gladysch.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace glady\Behind\Dependency\Configuration;

/**
 * Class DependencyConfigurationContainer
 * @package glady\Behind\Dependency\Configuration
 */
class DependencyConfigurationContainer
{

    /** @var DependencyConfiguration[] */
    private $configurations = array();


    public static function createFromArray(array $configArray)
    {
        $instance = new self;

        foreach ($configArray as $dependencyName => $dependencyConfiguration) {
            if (is_array($dependencyConfiguration)) {
                $dependencyConfiguration = DependencyConfiguration::createFromArray($dependencyConfiguration);
                $instance->addDependency($dependencyName, $dependencyConfiguration);
            }
            else if ($dependencyConfiguration instanceof DependencyConfiguration) {
                $instance->addDependency($dependencyName, $dependencyConfiguration);
            }
            else {
                throw new \RuntimeException(
                    "Configuration for dependency named '$dependencyName' is neither an array"
                    . " nor an instance of 'DependencyConfiguration'"
                );
            }
        }

        return $instance;
    }


    /**
     * @param string                  $dependencyName
     * @param DependencyConfiguration $dependencyConfiguration
     */
    public function addDependency($dependencyName, DependencyConfiguration $dependencyConfiguration)
    {
        $this->configurations[$dependencyName] = $dependencyConfiguration;
    }


    /**
     * @param string $dependencyName
     * @return DependencyConfiguration|null
     */
    public function getDependency($dependencyName)
    {
        return isset($this->configurations[$dependencyName])
            ? $this->configurations[$dependencyName]
            : null;

    }


    /**
     * @return array
     */
    public function getDependencyNames()
    {
        $dependencyConfigurations = $this->configurations;
        return array_keys($dependencyConfigurations);
    }
}
