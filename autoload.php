<?php
/*
 * This file is part of the Behind-Project (https://github.com/glady/Behind).
 *
 * (c) Mike Gladysch <mail@mike-gladysch.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
