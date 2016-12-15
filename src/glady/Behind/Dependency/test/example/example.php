<?php

use glady\Behind\Dependency\Configuration\DependencyConfigurationContainer;
use glady\Behind\Dependency\Injection\PublicDependencyInjectionContainer;

require __DIR__ . '/../../../../../../autoload.php';

// build dependency container by config

/** @var DependencyConfigurationContainer $dependencyConfigurationContainer */
$dependencyConfigurationContainer = require __DIR__ . '/config/config.dependency.php';
// "Public" extension enables direct calls on "get". Better is to write own extension with explicit "getSession" and "getRequest"
$dependencyContainer = new PublicDependencyInjectionContainer($dependencyConfigurationContainer);

// get session
$session = $dependencyContainer->get('session');

// Test code output:
/*
 * init request
 * Session with someRequestParam '' started!
 */
// description:
/*
 * 1. get session creates session instance and calls initialize callback, which call $session->start()
 * 2. on session start, the session instance asks by SessionDependencyWrapper::getSomeRequestParam for the correct identifier of client
 * 3. this function calls get "request" on dependency container, which creates and initializes the request object (before session start is finished)
 */
