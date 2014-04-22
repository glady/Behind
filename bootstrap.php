<?php
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