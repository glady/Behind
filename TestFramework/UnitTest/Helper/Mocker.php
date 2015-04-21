<?php
/*
 * This file is part of the Behind-Project (https://github.com/glady/Behind).
 *
 * (c) Mike Gladysch <mail@mike-gladysch.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace glady\Behind\TestFramework\UnitTest\Helper;

/**
 * Class Mocker
 * @package glady\Behind\TestFramework\UnitTest\Helper
 */
class Mocker
{
    /** @var callable[] */
    private static $mockedMethods = array();


    /**
     * @param string $function
     * @param string $namespace
     */
    public static function makeGlobalFunctionMockable($function, $namespace)
    {
        $php = "namespace $namespace
        {
            function $function(\$callable) {
                \$mocker = new \\glady\\Behind\\TestFramework\\UnitTest\\Helper\\Mocker();
                return \$mocker->callMockedFunction('$function', '$namespace', array(\$callable));
            }
        }";

        eval($php);
    }


    /**
     * @param string   $function
     * @param string   $namespace
     * @param callable $callable
     * @throws \Exception
     */
    public function mockGlobalFunctionForNamespace($function, $namespace, $callable)
    {
        if (!function_exists("$namespace\\$function")) {
            // defining here would cause ClassLoaderTest failing for each of the spl_autoload_register mocks.
//            self::makeGlobalFunctionMockable($function, $namespace);

            throw new \Exception(
                "Function '$namespace\\$function' has to be defined at the beginning of php-process"
                . " by Mocker::makeGlobalFunctionMockable()"
            );
        }
        self::$mockedMethods["$namespace\\$function"] = $callable;
    }


    /**
     * @param string $function
     * @param string $namespace
     * @param array  $args
     * @return mixed
     */
    public function callMockedFunction($function, $namespace, $args)
    {
        // if is mocked
        if ($this->isGlobalFunctionMockedForNamespace($function, $namespace)) {
            // call mock
            $callable = self::$mockedMethods["$namespace\\$function"];
        }
        else {
            // call original
            $callable = "\\$function";
        }
        return call_user_func_array($callable, $args);
    }


    /**
     * @param $function
     * @param $namespace
     * @return bool
     */
    public function isGlobalFunctionMockedForNamespace($function, $namespace)
    {
        return isset(self::$mockedMethods["$namespace\\$function"]);
    }


    /**
     * @param string $function
     * @param string $namespace
     */
    public function removeMockOfGlobalFunctionForNamespace($function, $namespace)
    {
        unset(self::$mockedMethods["$namespace\\$function"]);
    }


    /**
     * remove all mocks
     *  - all global function mocks for each namespace
     */
    public function reset()
    {
        self::$mockedMethods = array();
    }
}
