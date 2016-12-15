<?php

use glady\Behind\Dependency\Configuration\DependencyConfigurationContainer;

// random test both ways for config
$option = mt_rand(1, 2);
switch ($option) {
    // example 1: base array configuration
    case 1:
        return DependencyConfigurationContainer::createFromArray(require __DIR__ . '/config.dependency.array-notation.php');

    // example 2: explicit object construction
    case 2:
        $dependencyConfigurationContainer = new DependencyConfigurationContainer();
        $dependencyConfigurationContainer->addDependency('session', require __DIR__ . '/config.dependency.session.php');
        $dependencyConfigurationContainer->addDependency('request', require __DIR__ . '/config.dependency.request.php');
        return $dependencyConfigurationContainer;
}

throw new RuntimeException("invalid option '$option'");

