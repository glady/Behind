<?php
require __DIR__ . '/../ClassLoader.php';

use glady\Behind\ClassLoader\ClassLoader;

$classLoader = new ClassLoader();
$classLoader->addClassMap(array(
    'Helper_Database' => '/dir/somewhere/shared/lib/Helper/Database.php'
));
$testDir = __DIR__ . '/test';
$classLoader->addSeparatorClassLoaderRule($testDir, '_');               // "old style class names"
$classLoader->addSeparatorClassLoaderRule($testDir, '_', array(), '_'); // "old style class names" with mapping "_<class>" to sub-dir
$classLoader->addNamespaceClassLoaderRule($testDir);                    // default namespace to folder

//$classLoader->on(ClassLoader::ON_BEFORE_LOAD, function ($invoker, $eventName, $eventData) {
//    var_dump($eventName, $eventData);
//});
//$classLoader->on(ClassLoader::ON_BEFORE_REQUIRE, function ($invoker, $eventName, $eventData) {
//    var_dump($eventName, $eventData);
//});
//$classLoader->on(ClassLoader::ON_RULE_DOES_NOT_MATCH, function ($invoker, $eventName, $eventData) {
//    var_dump($eventName, $eventData);
//});
//$emptyClassBody = 'public function __call($m, $a) {} public function __toString() {return __CLASS__;}';
//$classLoader->on(ClassLoader::ON_AFTER_LOAD, function ($invoker, $eventName, $eventData) use ($emptyClassBody) {
//    if ($eventData[ClassLoader::LOAD_STATE_LOADED] === false) {
//        $className = $eventData[ClassLoader::LOAD_STATE_CLASS_NAME];
//        $lastNsSepPos = strrpos($className, '\\');
//        $classHeader = '';
//        if ($lastNsSepPos !== false) {
//            if ($lastNsSepPos > 0) {
//                $namespace = substr($className, 1, $lastNsSepPos - 1);
//                $classHeader = "namespace $namespace;";
//            }
//            $className = substr($className, $lastNsSepPos + 1);
//        }
//        eval("
//            $classHeader
//            class $className { $emptyClassBody }
//        ");
////        $json = json_encode($eventData);
////        throw new \Exception("Class not found. load state = $json");
//    }
//});

$classLoader->loadClass('\glady\Core\Kernel');
$classLoader->loadClass('TestFolder_TestClass');

$classLoader->register();

$testClass = new \Helper_Database();

echo "\n $testClass \n\n";