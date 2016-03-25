<?php
/*
 * This file is part of the Behind-Project (https://github.com/glady/Behind).
 *
 * (c) Mike Gladysch <mail@mike-gladysch.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once __DIR__ . "/src/glady/Behind/ClassLoader/ClassLoader.php";
use glady\Behind\ClassLoader\ClassLoader;

$classLoader = new ClassLoader();
$namespaces = array(
    'glady' => __DIR__ . '/src/glady'
);
$classLoader->addNamespaceClassLoaderRule(null, $namespaces);
$classLoader->register();

// Debugging class loading example:
//$classLoader->on('all', function($me, $event, $state) {
//    echo "\n $event - " . json_encode($state);
//});

\glady\Behind::setClassLoader($classLoader);
