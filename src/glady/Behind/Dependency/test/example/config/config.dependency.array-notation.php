<?php

use glady\Behind\Dependency\Configuration\DependencyConfiguration;
use glady\Behind\Dependency\test\example\ExampleClasses\Request;
use glady\Behind\Dependency\test\example\ExampleClasses\Session;
use glady\Behind\Dependency\test\example\ExampleClasses\SessionDependencyWrapper;

return array(
    'session' => array(
        DependencyConfiguration::CONFIG_CLASS => Session::CLASS_NAME,
        DependencyConfiguration::CONFIG_WRAPPER => SessionDependencyWrapper::CLASS_NAME,
        DependencyConfiguration::CONFIG_SHARED => true,
        DependencyConfiguration::CONFIG_INITIALIZE_CALLBACK => function (Session $session) {
            $session->start();
        }
    ),
    'request' => array(
        DependencyConfiguration::CONFIG_CLASS => Request::CLASS_NAME,
        DependencyConfiguration::CONFIG_WRAPPER => null,
        DependencyConfiguration::CONFIG_SHARED => true,
        DependencyConfiguration::CONFIG_INITIALIZE_CALLBACK => function (Request $request) {
            echo "init request\n";
            if (\glady\Behind::isCli()) {
                global $argv;
                foreach ($argv as $i => $arg) {
                    if ($arg === '--get') {
                        list($getParam, $value) = explode("=", $argv[$i + 1]);
                        $_GET[$getParam] = $value;
                    }
                }
            }
            $request->setPostParams($_POST);
            $request->setGetParams($_GET);
            $request->setCookies($_COOKIE);
            //$request->setHeaders(apache_request_headers());
        }
    )
);
