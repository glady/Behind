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
//    const LOAD_STATE_RULE           = 'rule';
    const LOAD_STATE_LOADED         = 'loaded';

    /** Config constants */
    const CONFIG_LOAD_RULE_ORDERED  = 'config_rules';
    //</editor-fold>

    //<editor-fold desc="Class Variables">
    /** @var array */
    protected $config = array();

    /** @var callable[] */
    protected $events = array();

    /** @var array */
    protected $rememberedLoadedClasses = array();

    /** @var int */
    protected $autoIndex = 1;
    //</editor-fold>


    //<editor-fold desc="Registering as autoloader">
    /**
     * @param object $autoLoader - instance of any classLoader class with loading classes within $fn
     * @param string $fn
     */
    public static function registerAutoLoader($autoLoader = null, $fn = self::LOAD_FUNCTION)
    {
        if ($autoLoader === null) {
            $autoLoader = new self();
        }
        spl_autoload_register(array($autoLoader, $fn));
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
        return class_exists($className, false) || interface_exists($className, false);
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
     * @param $fileName
     */
    protected function includeFile($fileName)
    {
        include $fileName;
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
                $fileName = $this->getFileNameByRule($rule, $className);

                $state[self::LOAD_STATE_FILE_NAME] = $fileName;

                if ($fileName !== null && $this->fileExists($fileName)) {
                    $this->fire(self::ON_BEFORE_REQUIRE, $state);

                    $this->includeFile($fileName);

                    if ($this->classExists($className)) {
                        $this->rememberLoadedClass($className, $fileName);
                        $state[self::LOAD_STATE_LOADED] = true;
                        $this->fire(self::ON_AFTER_REQUIRE, $state);
                    }
                }
                else {
                    if ($this->classExists($className)) {
                        // possibly explicit loaded/defined by callback rule without including a file
                        $state[self::LOAD_STATE_LOADED] = true;
                    }
                    else {
                        $this->fire(self::ON_RULE_DOES_NOT_MATCH, $state);
                        $fileName = null;
                    }
                }
            }
        }

        $this->fire(self::ON_AFTER_LOAD, $state);

        return $state[self::LOAD_STATE_LOADED];
    }


    /**
     * @param string $className
     * @param string $fileName
     */
    private function rememberLoadedClass($className, $fileName)
    {
        $this->rememberedLoadedClasses[$className] = $fileName;
    }


    /**
     * @param array $rule
     * @param string $className
     * @return string|null
     */
    private function getFileNameByRule(array $rule, $className)
    {
        $fileName = null;
        if (isset($rule['type'])) {
            switch ($rule['type']) {
                case 'callback':
                    $fileName = call_user_func_array($rule['fn'], array($className));
                    break;

                case 'map':
                    if (isset($rule['classes'][$className])) {
                        $fileName = $rule['classes'][$className];
                        if (isset($rule['baseDir'])) {
                            $fileName = $rule['baseDir'] . DIRECTORY_SEPARATOR . $fileName;
                        }
                    }
                    break;

                case 'separator':
                    $sep    = isset($rule['separator']) ? $rule['separator']    : '\\';
                    $fixed  = isset($rule['fixed'])     ? $rule['fixed']        : array();
                    $root   = isset($rule['root'])      ? $rule['root']         : __DIR__;

                    $originalClassName = $className;
                    $matchingLength = 0;
                    foreach ($fixed as $fixedClassNamePart => $fixedDir) {
                        $length = strlen($fixedClassNamePart);
                        if ($length > $matchingLength && strpos($originalClassName, $fixedClassNamePart) === 0) {
                            $className = substr($originalClassName, $length);
                            $matchingLength = $length;
                            $root = $fixedDir;
                        }
                    }

                    $relativeFileName = str_replace($sep, DIRECTORY_SEPARATOR, $className);

                    $root = rtrim($root, DIRECTORY_SEPARATOR);
                    $relativeFileName = ltrim($relativeFileName, DIRECTORY_SEPARATOR);

                    $fileName = $root . DIRECTORY_SEPARATOR . $relativeFileName . '.php';
                    break;

                case 'separator-with-subdir-prefix':
                    $subDirPrefix = isset($rule['subDirPrefix']) ? $rule['subDirPrefix']  : '_';
                    unset($rule['subDirPrefix']);
                    $rule['type'] = 'separator';
                    $separatorFileName = $this->getFileNameByRule($rule, $className);
                    if (substr($separatorFileName, -5) === DIRECTORY_SEPARATOR . '.php') {
                        $lastNamespacePart = substr($className, strrpos($className, '\\') + 1);
                        $fileName = substr($separatorFileName, 0, -4) . $subDirPrefix . $lastNamespacePart . '.php';
                    }
                    else {
                        $filePos = strrpos($separatorFileName, DIRECTORY_SEPARATOR);
                        if ($filePos !== false) {
                            $fileName = substr($separatorFileName, 0, $filePos) . DIRECTORY_SEPARATOR
                                . substr($separatorFileName, $filePos + 1, -4) . DIRECTORY_SEPARATOR
                                . $subDirPrefix . substr($separatorFileName, $filePos + 1);
                        }
                    }
                    break;
            }
        }

        return $fileName;
    }
    //</editor-fold>


    //<editor-fold desc="Pseudo-Event handling">
    /**
     * @return array
     */
    public function getDefinedEventNames()
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
            $eventNames = $this->getDefinedEventNames();
            foreach ($eventNames as $eventName) {
                $this->addEventListener($eventName, $callable, $options, $name);
            }
            return;
        }

        $overwrite  = isset($options['overwrite'])  && (bool)$options['overwrite'];
        if ($overwrite === true || !isset($this->events[$eventName])) {
            $this->events[$eventName] = array();
        }

        // dispatching configuration
        $break      = isset($options['breakEventOnReturnFalse']) && (bool)$options['breakEventOnReturnFalse'];

        // how many times this will be called?
        $single     = isset($options['single'])     && (bool)$options['single'];
        $count      = $single ? 1 : (isset($options['count']) ? (int)$options['count'] : -1);

        if ($name === null) {
            $name = "auto-indexed-listener-" . $this->autoIndex++;
        }

        // register event internally
        $this->events[$eventName][$name] = array('count' => $count, 'callable' => $callable, 'break' => $break);
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
        $return = call_user_func_array($eventCallable, array($this, $eventName, $eventData));
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
     * @param array $classMap
     */
    public function addClassMap(array $classMap, $baseDir = null)
    {
        if (!empty($classMap)) {
            $rules = $this->getConfig(self::CONFIG_LOAD_RULE_ORDERED, array());
            $rules[] = array(
                'type' => 'map',
                'classes' => $classMap,
                'baseDir' => $baseDir
            );
            $this->setConfig(self::CONFIG_LOAD_RULE_ORDERED, $rules);
        }
    }


    /**
     * @param callable $fn
     */
    public function addCallbackRule($fn)
    {
        if (is_callable($fn)) {
            $rules = $this->getConfig(self::CONFIG_LOAD_RULE_ORDERED, array());
            $rules[] = array(
                'type' => 'callback',
                'fn' => $fn
            );
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
        return isset($this->config[$configName]) ? $this->config[$configName] : $default;
    }


    /**
     * @param string $configName
     * @param mixed  $value
     */
    protected function setConfig($configName, $value)
    {
        if ($value === null) {
            unset($this->config[$configName]);
        }
        else {
            $this->config[$configName] = $value;
        }
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
}
