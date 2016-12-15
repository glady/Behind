<?php

use glady\Behind\Dependency\Configuration\DependencyConfiguration;
use glady\Behind\Dependency\test\example\ExampleClasses\Session;
use glady\Behind\Dependency\test\example\ExampleClasses\SessionDependencyWrapper;

$sessionDependency = new DependencyConfiguration();
$sessionDependency->setClass(Session::CLASS_NAME);
$sessionDependency->setWrapper(SessionDependencyWrapper::CLASS_NAME);
$sessionDependency->setInitializeCallback(function (Session $session) {
    $session->start();
});
return $sessionDependency;
