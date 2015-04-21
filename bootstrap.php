<?php
require __DIR__ . '/TestFramework/UnitTest/Helper/Mocker.php';
use glady\Behind\TestFramework\UnitTest\Helper\Mocker;
// global functions have to be defined before classes using them!
Mocker::makeGlobalFunctionMockable('spl_autoload_register', 'glady\Behind\ClassLoader');

require_once __DIR__ . "/ClassLoader/ClassLoader.php";
use glady\Behind\ClassLoader\ClassLoader;

$classLoader = new ClassLoader();
$namespaces = array(
    'glady\Behind' => __DIR__
);
$classLoader->addNamespaceClassLoaderRule(__DIR__, $namespaces);
$classLoader->addNamespaceClassLoaderRule(__DIR__, $namespaces, '_');
$classLoader->register();

\glady\Behind::setClassLoader($classLoader);
