<?php
require_once __DIR__ . "/ClassLoader/ClassLoader.php";
use glady\Behind\ClassLoader\ClassLoader;

$classLoader = new ClassLoader();
$classLoader->addNamespaceClassLoaderRule(__DIR__, array("\\glady" => __DIR__ . '/..'));
$classLoader->addNamespaceClassLoaderRule(__DIR__, array("\\glady" => __DIR__ . '/..'), '_');
$classLoader->register();