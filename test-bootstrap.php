<?php
require __DIR__ . '/TestFramework/UnitTest/Helper/Mocker.php';
use glady\Behind\TestFramework\UnitTest\Helper\Mocker;
// global functions have to be defined before classes using them!
Mocker::makeGlobalFunctionMockable('spl_autoload_register', 'glady\Behind\ClassLoader');

require 'autoload.php';
