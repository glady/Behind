<?php
/*
 * This file is part of the Behind-Project (https://github.com/glady/Behind).
 *
 * (c) Mike Gladysch <mail@mike-gladysch.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace glady\Behind\ClassLoader;

use ArrayAccess;

/**
 * Class ClassLoader
 */
class ClassLoader
{
    const CLASSNAME = __CLASS__;

    //<editor-fold desc="Constants">
    /** mapping constants */
    const LOAD_FUNCTION             = 'loadClass';

    /** Event constants */
    const ON_BEFORE_LOAD            = 'before_load';
    const ON_AFTER_LOAD             = 'after_load';
    const ON_RULE_DOES_NOT_MATCH    = 'rule_does_not_match';
    const ON_BEFORE_REQUIRE         = 'before_require';
    const ON_AFTER_REQUIRE          = 'after_require';
    const ON_ALL                    = 'all';

    /** load state constants */
    const LOAD_STATE_CLASS_NAME     = 'className';
    const LOAD_STATE_FILE_NAME      = 'fileName';
    const LOAD_STATE_LOADED         = 'loaded';

    /** Config constants */
    const CONFIG_LOAD_RULE_ORDERED  = 'config_rules';
    //</editor-fold>

    //<editor-fold desc="Class Variables">
    /** @var array */
    protected $config = array();

    /** @var callable[] */
    protected $events = array();

    /** @var int */
    protected $autoIndex = 1;

    /** @var string[] */
    private $includedFiles = array();

    /** @var bool */
    private $compatibilityMode = false;
    //</editor-fold>


    //<editor-fold desc="Registering as autoloader">
    /**
     * @param object $autoLoader - instance of any classLoader class with loading classes within $fn
     * @param string $fn
     * @return object|ClassLoader
     */
    public static function registerAutoLoader($autoLoader = null, $fn = self::LOAD_FUNCTION)
    {
        if ($autoLoader === null) {
            $autoLoader = new self();
        }
        spl_autoload_register(array($autoLoader, $fn));
        return $autoLoader;
    }


    /**
     * registers this instance as one of the php auto-loader
     */
    public function register()
    {
        self::registerAutoLoader($this);
    }
    //</editor-fold>


    //<editor-fold desc="Class loading">
    /**
     * @param $className
     * @return bool
     */
    protected function classExists($className)
    {
        return class_exists($className, false) // classes - PHP 4
            || interface_exists($className, false) // interfaces - PHP_VERSION >= 5.0.2
            || function_exists('trait_exists') && trait_exists($className, false); // traits - PHP_VERSION >= 5.4
    }


    /**
     * @param $fileName
     * @return bool
     */
    protected function fileExists($fileName)
    {
        return file_exists($fileName);
    }


    /**
     * @param string $fileName
     * @param bool   $once
     * @return mixed
     */
    protected function includeFile($fileName, $once = false)
    {
        if ($once === true && $this->isCompatibilityMode()) {
            return include_once $fileName;
        }
        return include $fileName;
    }


    /**
     * @param string $className
     * @return bool
     */
    public function loadClass($className)
    {
        $state = array(
            self::LOAD_STATE_LOADED     => false,
            self::LOAD_STATE_CLASS_NAME => $className,
            self::LOAD_STATE_FILE_NAME  => null
        );

        $this->fire(self::ON_BEFORE_LOAD, $state);
        if (!$this->classExists($className)) {
            $autoloadRules = $this->getConfig(self::CONFIG_LOAD_RULE_ORDERED, array());
            while (!$state[self::LOAD_STATE_LOADED] && ($rule = array_shift($autoloadRules))) {
                $state = $this->tryToLoadClassByRule($state, $className, $rule);
            }
        }
        $this->fire(self::ON_AFTER_LOAD, $state);

        return $state[self::LOAD_STATE_LOADED];
    }


    /**
     * @param array $rule
     * @param string $className
     * @return string|null
     */
    private function getFileNameByRule(array $rule, $className)
    {
        $fileName = null;
        $type = $this->getFromArray($rule, 'type');
        switch ($type) {
            case 'callback':
                $fileName = call_user_func($rule['fn'], $className);
                break;

            case 'map':
                $classMap = $this->getFromArray($rule, 'classes', array());
                $baseDir = $this->getFromArray($rule, 'baseDir');
                $fileName = $this->getFileNameFromClassMap($className, $classMap, $baseDir);
                break;

            case 'separator':
            case 'separator-with-subdir-prefix':
                $separator = $this->getFromArray($rule, 'separator', '\\');
                $fixed     = $this->getFromArray($rule, 'fixed', array());
                $root      = $this->getFromArray($rule, 'root', null);

                $fileName = $this->getFileNameBySeparator($className, $separator, $root, $fixed);
                if ($type === 'separator') {
                    break;
                }
                $subDirPrefix = $this->getFromArray($rule, 'subDirPrefix', '_');
                $fileName = $this->applySubDirPrefixToFileName($fileName, $subDirPrefix, $className);
                break;
        }
        return $fileName;
    }


    /**
     * @param string $className
     * @param string $separator
     * @param string $root
     * @param array  $fixed
     * @return string
     */
    private function getFileNameBySeparator($className, $separator, $root, array $fixed = array())
    {
        if (($fixedNamespace = $this->getFixedNamespace($fixed, $className))) {
            $className = substr($className, strlen($fixedNamespace));
            $root = $fixed[$fixedNamespace];
        }
        else if ($root === null) {
            return null;
        }

        $relativeFileName = str_replace($separator, DIRECTORY_SEPARATOR, $className);
        $relativeFileName = ltrim($relativeFileName, DIRECTORY_SEPARATOR);
        $fileName = rtrim($root, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $relativeFileName . '.php';

        return $fileName;
    }


    /**
     * @param string $fileName
     * @param string $subDirPrefix
     * @param string $className
     * @return string
     */
    private function applySubDirPrefixToFileName($fileName, $subDirPrefix, $className)
    {
        $fixedRootPart = DIRECTORY_SEPARATOR . '.php';
        if (substr($fileName, -5) === $fixedRootPart) { // appears, when class = fixed namespace
            $namespaceParts = explode('\\', $className);
            $fixedRootBaseName = $subDirPrefix . array_pop($namespaceParts) . '.php';
            return str_replace($fixedRootPart, DIRECTORY_SEPARATOR . $fixedRootBaseName, $fileName);
        }
        return $this->mapFileNameToSubDirWithPrefix($fileName, $subDirPrefix);
    }


    /**
     * @param string $separatorFileName
     * @param string $subDirPrefix
     * @return string
     */
    private function mapFileNameToSubDirWithPrefix($separatorFileName, $subDirPrefix)
    {
        $root = dirname($separatorFileName);
        $basename = basename($separatorFileName);
        $newSubDir = substr($basename, 0, -4); // cut off ".php"
        $newBasename = $subDirPrefix . $basename;
        return $root . DIRECTORY_SEPARATOR . $newSubDir . DIRECTORY_SEPARATOR . $newBasename;
    }


    /**
     * @param string            $className
     * @param ArrayAccess|array $classMap
     * @param string|null       $baseDir
     * @return string
     */
    private function getFileNameFromClassMap($className, $classMap, $baseDir = null)
    {
        $fileName = $this->getFromArray($classMap, $className);
        if ($fileName && $baseDir) {
            $fileName = $baseDir . DIRECTORY_SEPARATOR . $fileName;
        }
        return $fileName;
    }


    /**
     * @param array  $fixed
     * @param string $className
     * @return int|null|string
     */
    private function getFixedNamespace(array $fixed, $className)
    {
        $longestNamespace = null;
        $matchingLength = 0;
        foreach ($fixed as $fixedClassNamePart => $fixedDir) {
            $length = strlen($fixedClassNamePart);
            if ($length > $matchingLength && strpos($className, $fixedClassNamePart) === 0) {
                $matchingLength = $length;
                $longestNamespace = $fixedClassNamePart;
            }
        }
        return $longestNamespace;
    }


    /**
     * @param array  $state
     * @param string $className
     * @param array  $rule
     * @return array
     */
    private function tryToLoadClassByRule(array $state, $className, array $rule)
    {
        $fileName = $this->getFileNameByRule($rule, $className);

        $state[self::LOAD_STATE_FILE_NAME] = $fileName;

        if ($fileName !== null && !$this->isFileAlreadyIncluded($fileName) && $this->fileExists($fileName)) {
            $this->fire(self::ON_BEFORE_REQUIRE, $state);

            if (!$this->classExists($className) && !$this->isFileAlreadyIncluded($fileName)) {
                $this->includeFile($fileName, true);
                $this->setFileIncluded($fileName);
            }

            if ($this->classExists($className)) {
                $state[self::LOAD_STATE_LOADED] = true;
                $this->fire(self::ON_AFTER_REQUIRE, $state);
            }
        }
        else {
            if ($this->classExists($className)) { // possibly explicit defined by callback rule without including a file
                $state[self::LOAD_STATE_LOADED] = true;
            }
            else {
                $this->fire(self::ON_RULE_DOES_NOT_MATCH, $state);
                $fileName = null;
            }
        }
        return $state;
    }
    //</editor-fold>


    //<editor-fold desc="Pseudo-Event handling">
    /**
     * @return array
     */
    protected function getDefinedEventNames()
    {
        return array(
            self::ON_BEFORE_LOAD,
            self::ON_AFTER_LOAD,
            self::ON_RULE_DOES_NOT_MATCH,
            self::ON_BEFORE_REQUIRE,
            self::ON_AFTER_REQUIRE
        );
    }


    /**
     * @param string   $eventName
     * @param callable $callable
     * @param array    $options
     * @param string   $name        [optional] is only needed when un-register an event is requested!
     */
    public function addEventListener($eventName, $callable, array $options = array(), $name = null)
    {
        if ($eventName === self::ON_ALL) {
            foreach ($this->getDefinedEventNames() as $eventName) {
                $this->addEventListener($eventName, $callable, $options, $name);
            }
            return;
        }
        if (!isset($this->events[$eventName]) || $this->getFromArray($options, 'overwrite', false) ) {
            $this->events[$eventName] = array();
        }

        // dispatching configuration
        $break      = (bool)$this->getFromArray($options, 'breakEventOnReturnFalse', false);
        // how many times this will be called?
        $single     = (bool)$this->getFromArray($options, 'single', false);
        $count      = $single ? 1 : $this->getFromArray($options, 'count', -1);
        if ($name === null) {
            $name = "auto-indexed-listener-" . $this->autoIndex++;
        }
        $this->events[$eventName][$name] = array('count' => (int)$count, 'callable' => $callable, 'break' => $break);
    }


    /**
     * @param string $eventName
     * @param string $name
     */
    public function removeEventListener($eventName, $name)
    {
        unset($this->events[$eventName][$name]);
    }


    /**
     * @param string $eventName
     * @param array  $eventData
     */
    protected function fire($eventName, array $eventData = array())
    {
        if (isset($this->events[$eventName])) {
            foreach ($this->events[$eventName] as $index => $eventDefinition) {
                $continueEvent = $this->executeEventCallback($eventName, $index, $eventData);
                if (!$continueEvent) {
                    return;
                }
            }
        }
    }


    /**
     * @param   string  $eventName
     * @param   int     $eventIndex
     * @param   array   $eventData
     * @return  bool
     */
    private function executeEventCallback($eventName, $eventIndex, array $eventData = array())
    {
        // execute callback
        $eventCallable = $this->events[$eventName][$eventIndex]['callable'];
        $return = call_user_func($eventCallable, $this, $eventName, $eventData);
        // decrease remaining runs
        $this->events[$eventName][$eventIndex]['count']--;
        if ($this->events[$eventName][$eventIndex]['count'] === 0) {
            unset($this->events[$eventName][$eventIndex]);
        }
        // stop event if it is breaking
        if ($return === false && $this->events[$eventName][$eventIndex]['break']) {
            return false;
        }
        return true;
    }
    //</editor-fold>


    //<editor-fold desc="Short-Cut functions for define rules">
    /**
     * @param array|null  $classMap
     * @param string|null $baseDir
     * @param string|null $file
     */
    public function addClassMap(array $classMap = null, $baseDir = null, $file = null)
    {
        if ($classMap === null && $file) {
            if ($this->fileExists($file)) {
                $classMap = $this->includeFile($file);
            }
        }

        if (!$classMap) {
            return;
        }

        $rules = $this->getConfig(self::CONFIG_LOAD_RULE_ORDERED, array());
        $rules[] = array('type' => 'map', 'classes' => $classMap, 'baseDir' => $baseDir);
        $this->setConfig(self::CONFIG_LOAD_RULE_ORDERED, $rules);
    }


    /**
     * @param callable $fn
     */
    public function addCallbackRule($fn)
    {
        if (is_callable($fn)) {
            $rules = $this->getConfig(self::CONFIG_LOAD_RULE_ORDERED, array());
            $rules[] = array('type' => 'callback', 'fn' => $fn);
            $this->setConfig(self::CONFIG_LOAD_RULE_ORDERED, $rules);
        }
    }


    /**
     * @param string $root
     * @param array  $fixedNamespaces
     * @param string $subDirPrefix
     */
    public function addNamespaceClassLoaderRule($root, array $fixedNamespaces = array(), $subDirPrefix = null)
    {
        $this->addSeparatorClassLoaderRule($root, '\\', $fixedNamespaces, $subDirPrefix);
    }


    /**
     * @param string $root
     * @param string $separator
     * @param array  $fixedNamespaces
     * @param string $subDirPrefix
     */
    public function addSeparatorClassLoaderRule($root, $separator, array $fixedNamespaces = array(), $subDirPrefix = null)
    {
        $rules = $this->getConfig(self::CONFIG_LOAD_RULE_ORDERED, array());
        $rule = array(
            'type' => 'separator',
            'separator' => $separator,
            'root' => $root,
            'fixed' => $fixedNamespaces
        );
        if ($subDirPrefix !== null) {
            $rule['type'] .= '-with-subdir-prefix';
            $rule['subDirPrefix'] = $subDirPrefix;
        }
        $rules[] = $rule;
        $this->setConfig(self::CONFIG_LOAD_RULE_ORDERED, $rules);
    }


    /**
     * @param string $configName
     * @param mixed  $default
     * @return mixed
     */
    protected function getConfig($configName, $default = null)
    {
        return $this->getFromArray($this->config, $configName, $default);
    }


    /**
     * @param string $configName
     * @param mixed  $value
     */
    protected function setConfig($configName, $value)
    {
        $this->config[$configName] = $value;
    }
    //</editor-fold>


    //<editor-fold desc="Short-Cut functions for event (un-)registering">
    /**
     * Short-Named function for addEventListener
     *
     * @param string   $eventName
     * @param callable $callable
     * @param array    $options
     * @param string   $name        [optional] is only needed when un-register an event is requested!
     */
    public function on($eventName, $callable, array $options = array(), $name = null)
    {
        $this->addEventListener($eventName, $callable, $options, $name);
    }


    /**
     * Short-Named function for removeEventListener
     *
     * @param string $eventName
     * @param string $name
     */
    public function un($eventName, $name)
    {
        $this->removeEventListener($eventName, $name);
    }
    //</editor-fold>


    /**
     * Helper function for accessing array keys, that are possible not set.
     *
     * @param array      $array
     * @param string|int $key
     * @param mixed      $default
     * @return mixed
     */
    private function getFromArray(array $array, $key, $default = null)
    {
        return isset($array[$key])
            ? $array[$key]
            : $default;
    }


    /**
     * @param string $fileName
     * @return bool
     */
    protected function isFileAlreadyIncluded($fileName)
    {
        return isset($this->includedFiles[$fileName]);
    }


    /**
     * @param string $fileName
     */
    private function setFileIncluded($fileName)
    {
        $this->includedFiles[$fileName] = true;
    }


    /**
     * This function registers rules for composer-classmap, psr-0 autoloading and psr-4 autoloading.
     * Not supported: custom autoloading by own file and include paths.
     *
     * used and not used files:
     *  $vendorPath
     *    `- composer
     *      `- autoload_classmap.php    < used
     *      `- autoload_files.php       < NOT used (own autoload-handling of other projects)
     *      `- autoload_namespaces.php  < used (PSR-0)
     *      `- autoload_psr4.php        < used (PSR-4)
     *      `- autoload_real.php        < NOT used (internal configurator of ClassLoader.php)
     *      `- ClassLoader.php          < NOT used (autoloading by composer)
     *      `- include_paths.php        < NOT used
     *
     * @param string $vendorPath
     */
    public function addComposerVendorAutoloadRules($vendorPath)
    {
        $this->addClassMap(null, null, $vendorPath . '/composer/autoload_classmap.php');
        $this->addPsr0Rules(null, $vendorPath . '/composer/autoload_namespaces.php');
        $this->addPsr4Rules(null, $vendorPath . '/composer/autoload_psr4.php');
    }


    /**
     * @param array[]|null $namespaces
     * @param string|null $file
     */
    protected function addPsr0Rules(array $namespaces = null, $file = null)
    {
        if ($namespaces === null && $file) {
            if ($this->fileExists($file)) {
                $namespaces = $this->includeFile($file);
            }
        }

        if (!$namespaces) {
            return;
        }

        $rules = array(
            array()
        );

        foreach ($namespaces as $namespace => $srcPaths) {
            foreach ($srcPaths as $path) {
                $psr0Path = str_replace('\\', '/', $namespace);
                $realClassPath = $path . '/' . $psr0Path;
                $this->applyNamespaceWithPathToRules($rules, $namespace, $realClassPath);
            }
        }

        foreach ($rules as $rule) {
            $this->addNamespaceClassLoaderRule(null, $rule);
            $this->addSeparatorClassLoaderRule(null, '_', $rule);
        }
    }


    /**
     * @param array[]|null $namespaces
     * @param string|null $file
     */
    protected function addPsr4Rules(array $namespaces = null, $file = null)
    {
        if ($namespaces === null && $file) {
            if ($this->fileExists($file)) {
                $namespaces = $this->includeFile($file);
            }
        }

        if (!$namespaces) {
            return;
        }

        $rules = array(
            array()
        );

        foreach ($namespaces as $namespace => $srcPaths) {
            foreach ($srcPaths as $path) {
                $this->applyNamespaceWithPathToRules($rules, $namespace, $path);
            }
        }

        foreach ($rules as $rule) {
            $this->addNamespaceClassLoaderRule(null, $rule);
            $this->addSeparatorClassLoaderRule(null, '_', $rule);
        }
    }

    /**
     * @param array  &$rules
     * @param string $namespace
     * @param string $realClassPath
     */
    protected function applyNamespaceWithPathToRules(array &$rules, $namespace, $realClassPath)
    {
        $ruleIndex = 0;
        while (isset($rules[$ruleIndex][$namespace])) {
            $ruleIndex++;
            if (!isset($rules[$ruleIndex])) {
                $rules[$ruleIndex] = array();
                break;
            }
        }
        $rules[$ruleIndex][$namespace] = $realClassPath;
    }


    public function isCompatibilityMode()
    {
        return $this->compatibilityMode;
    }


    /**
     * @param bool $compatibilityMode
     * @return $this
     */
    public function setCompatibilityMode($compatibilityMode = true)
    {
        $this->compatibilityMode = $compatibilityMode === true;
        return $this;
    }

}
