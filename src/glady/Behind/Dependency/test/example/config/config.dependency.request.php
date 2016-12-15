<?php

use glady\Behind\Dependency\Configuration\DependencyConfiguration;
use glady\Behind\Dependency\test\example\ExampleClasses\Request;

$requestDependency = new DependencyConfiguration();
$requestDependency->setClass(Request::CLASS_NAME);
$requestDependency->setInitializeCallback(function (Request $request) {
    echo "init request\n";
    $request->setGetParams($_GET);
    $request->setPostParams($_POST);
    $request->setCookies($_COOKIE);
    //$request->setHeaders(apache_request_headers());
});
return $requestDependency;
