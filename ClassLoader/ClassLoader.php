<?php
/*
 * TODO license
 */

namespace glady\Behind\ClassLoader;


/**
 * Class ClassLoader
 */
class ClassLoader
{

    /** mapping constants */
    const LOAD_FUNCTION             = 'loadClass';

    /** Event constants */
    const ON_BEFORE_LOAD            = 'before_load';
    const ON_AFTER_LOAD             = 'after_load';
    const ON_RULE_DOES_NOT_MATCH    = 'rule_does_not_match';
    const ON_BEFORE_REQUIRE         = 'before_require';
    const ON_AFTER_REQUIRE          = 'after_require';

    /** load state constants */
    const LOAD_STATE_CLASS_NAME     = 'className';
    const LOAD_STATE_FILE_NAME      = 'fileName';
//    const LOAD_STATE_RULE           = 'rule';
    const LOAD_STATE_LOADED         = 'loaded';

    /** Config constants */
    const CONFIG_LOAD_RULE_ORDERED  = 'config_rules';

    /** @var array */
    protected $config = array();

    /** @var callable[] */
    protected $events = array();

    /** @var array */
    protected $rememberedLoadedClasses = array();


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


    /**
     * @param string $className
     */
    public function loadClass($className)
    {
        // initial event data array, loaded false and requested class name
        $state = array(
            self::LOAD_STATE_LOADED     => false,
            self::LOAD_STATE_CLASS_NAME => $className,
//            self::LOAD_STATE_RULE       => null,
            self::LOAD_STATE_FILE_NAME  => null
        );

        // fire before load
        $this->fire(self::ON_BEFORE_LOAD, $state);

        $autoloadRules = $this->getConfig(self::CONFIG_LOAD_RULE_ORDERED, array());
        while (!$state[self::LOAD_STATE_LOADED] && ($rule = array_shift($autoloadRules))) {
            $fileName = $this->getFileNameByRule($rule, $className);

//            $state[self::LOAD_STATE_RULE] = $rule;
            $state[self::LOAD_STATE_FILE_NAME] = $fileName;

            if ($fileName !== null && file_exists($fileName)) {
                $this->fire(self::ON_BEFORE_REQUIRE, $state);

                // require file
                include $fileName;

                // check if include was successful - set
                if (class_exists($className, false)) {
                    $this->rememberLoadedClass($className, $fileName);
                    $state[self::LOAD_STATE_LOADED] = true;
                    $this->fire(self::ON_AFTER_REQUIRE, $state);
                }
            }
            else {
                $this->fire(self::ON_RULE_DOES_NOT_MATCH, $state);
                $fileName = null;
            }
        }

        $this->fire(self::ON_AFTER_LOAD, $state);
    }


    /**
     * @param string $className
     * @param string $fileName
     */
    public function rememberLoadedClass($className, $fileName)
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
                case 'map':
                    if (isset($rule['classes'][$className])) {
                        $fileName = $rule['classes'][$className];
                    }
                    break;

                case 'separator':
                    $sep    = isset($rule['separator']) ? $rule['separator']    : '\\';
                    $fixed  = isset($rule['fixed'])     ? $rule['fixed']        : array();
                    $root   = isset($rule['root'])      ? $rule['root']         : __DIR__;

                    foreach ($fixed as $fixedClassNamePart => $fixedDir) {
                        if (strpos($className, $fixedClassNamePart) !== 0) {
                            $className = substr($className, strlen($fixedClassNamePart));
                            $root = $fixedDir;
                            break;
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
                    $filePos = strrpos($separatorFileName, DIRECTORY_SEPARATOR);
                    if ($filePos !== false) {
                        $fileName = substr($separatorFileName, 0, $filePos) . DIRECTORY_SEPARATOR
                            . substr($separatorFileName, $filePos + 1, -4) . DIRECTORY_SEPARATOR
                            . $subDirPrefix . substr($separatorFileName, $filePos + 1);

                    }
                    break;
            }
        }

        return $fileName;
    }


    /**
     * @param string $eventName
     * @param array  $eventData
     */
    private function fire($eventName, array $eventData = array())
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


    /**
     * @param string   $eventName
     * @param callable $callable
     * @param array    $options
     */
    public function on($eventName, $callable, array $options = array())
    {
        $overwrite  = isset($options['overwrite'])  && (bool)$options['overwrite'];
        if ($overwrite === true || !isset($this->events[$eventName])) {
            $this->events[$eventName] = array();
        }

        // dispatching configuration
        $break      = isset($options['breakEventOnReturnFalse']) && (bool)$options['breakEventOnReturnFalse'];

        // how many times this will be called?
        $single     = isset($options['single'])     && (bool)$options['single'];
        $count      = $single ? 1 : (isset($options['count']) ? (int)$options['count'] : -1);

        // register event internally
        $this->events[$eventName][] = array('count' => $count, 'callable' => $callable, 'break' => $break);
    }


    /**
     * @param string $configName
     * @param mixed  $default
     * @return mixed
     */
    public function getConfig($configName, $default = null)
    {
        return isset($this->config[$configName]) ? $this->config[$configName] : $default;
    }


    /**
     * @param string $configName
     * @param mixed  $value
     */
    public function setConfig($configName, $value)
    {
        if ($value === null) {
            unset($this->config[$configName]);
        }
        else {
            $this->config[$configName] = $value;
        }
    }


    /**
     * @param array $classMap
     */
    public function addClassMap(array $classMap)
    {
        if (!empty($classMap)) {
            $rules = $this->getConfig(self::CONFIG_LOAD_RULE_ORDERED, array());
            $rules[] = array(
                'type' => 'map',
                'classes' => $classMap
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
}
