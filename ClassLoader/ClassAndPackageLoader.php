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

// require base class of class loader, because no other autoloader should be required
require_once __DIR__ . '/ClassLoader.php';

/**
 * Class ClassAndPackageLoader
 * @package glady\Behind\ClassLoader
 */
class ClassAndPackageLoader extends ClassLoader
{
    /** @var string */
    protected $packageFilePath = __DIR__;

    /** @var string */
    protected $version = 'trunk';

    /** @var array */
    protected $packages = array();

    /** @var array */
    protected $ignores = array();

    /** @var bool */
    protected $isLoadPackageEnabled = true;

    /** @var bool */
    protected $isSavePackageEnabled = true;


    /**
     * this function enables loading and saving of packages. calling with one argument, both will be toggled, when two
     *  arguments are given, the first toggles loading and the second toggles saving
     *
     * @example setPackagingEnabled(true)         will enable loading and saving of packages
     * @example setPackagingEnabled(true, true)   will enable loading and saving of packages
     * @example setPackagingEnabled(false)        will disable loading and saving of packages (behavior of parent class)
     * @example setPackagingEnabled(false, false) will disable loading and saving of packages (behavior of parent class)
     * @example setPackagingEnabled(false, true)  will disable loading, but will save package on stopPackage!
     *                                              `- can be used to regenerate packages
     * @example setPackagingEnabled(true, false)  will enable loading, but will never save package on stopPackage!
     *
     * @param bool $enabled
     * @param null|bool $saveEnabled
     */
    public function setPackagingEnabled($enabled = true, $saveEnabled = null)
    {
        if ($saveEnabled === null) {
            $saveEnabled = $enabled;
        }
        $this->isLoadPackageEnabled = $enabled === true;
        $this->isSavePackageEnabled = $saveEnabled === true;
    }


    /**
     * @param string $version
     */
    public function setVersion($version)
    {
        $this->version = $version;
    }


    /**
     * @param string $packageFilePath
     */
    public function setPackageFilePath($packageFilePath)
    {
        $this->packageFilePath = $packageFilePath;
    }


    /**
     * @param string $id
     */
    public function startPackage($id)
    {
        if ($this->hasStoredPackage($id) && $this->isLoadPackageEnabled) {
            $this->includeStoredPackage($id);
        }
        else if ($this->isSavePackageEnabled) {
            $this->packages[$id] = array(
                'active' => true,
                'classMap' => array()
            );

            $this->on(self::ON_AFTER_LOAD, function (ClassAndPackageLoader $me, $eventName, $eventData) use ($id) {
                if ($eventData[ClassLoader::LOAD_STATE_LOADED] === true) {
                    $className = $eventData[ClassLoader::LOAD_STATE_CLASS_NAME];
                    $fileName = $eventData[ClassLoader::LOAD_STATE_FILE_NAME];
                    $me->addClassToPackage($id, $className, $fileName, "package-$id");
                }
            });
        }
    }


    /**
     * @param string $id
     * @param string $className
     * @param string $fileName
     */
    public function addClassToPackage($id, $className, $fileName)
    {
        if ($this->isPackageActive($id)) {
            $this->packages[$id]['classMap'][$className] = $fileName;
        }
    }


    /**
     * @param string $id
     */
    public function stopPackage($id)
    {
        if ($this->isSavePackageEnabled && $this->isPackageActive($id)) {
            $this->packages[$id]['active'] = false;
            $this->writeStoredPackage($id);
            $this->un(self::ON_AFTER_LOAD, "package-$id");
        }
    }


    /**
     * @param string $id
     * @return bool
     */
    private function hasStoredPackage($id)
    {
        $packageFilename = $this->getPackageFilename($id);
        return file_exists("$this->packageFilePath/$packageFilename");
    }


    /**
     * @param string $id
     */
    private function includeStoredPackage($id)
    {
        $packageFilename = $this->getPackageFilename($id);
        $filename = "$this->packageFilePath/$packageFilename";
        $state = array(
            self::LOAD_STATE_LOADED     => false,
            self::LOAD_STATE_CLASS_NAME => null,
            self::LOAD_STATE_FILE_NAME  => $filename
        );
        $this->fire(self::ON_BEFORE_LOAD, $state);

        if (file_exists($filename)) {
            $this->fire(self::ON_BEFORE_REQUIRE, $state);

            include $filename;

            $state[self::LOAD_STATE_LOADED] = true;
            $this->fire(self::ON_AFTER_REQUIRE, $state);
        }
        $this->fire(self::ON_AFTER_LOAD, $state);
    }


    /**
     * @param string $id
     */
    private function writeStoredPackage($id)
    {
        $meta = "";
        $package = "<?php\n";
        $ignoredCount = 0;
        foreach ($this->packages[$id]['classMap'] as $className => $fileName) {
            if ($this->isClassNameIgnoredForPackage($className)) {
                $ignoredCount++;
                continue;
            }
            $meta .= "\n  \"$className\": \"$fileName\",";

            // load file content
            $sourceFileContent = file_get_contents($fileName);
            $sourceFileContent = str_replace("\r", "", $sourceFileContent);
            $sourceFileContent = explode("\n", $sourceFileContent);

            // walk through lines for collecting right namespaces
            $package .= $this->normalizeNamespaceForPackage($sourceFileContent, $className, $fileName);
        }

        $meta = "{\n \"count\": " . count($this->packages[$id]['classMap']) . ","
            . ($ignoredCount > 0 ? "\n \"ignored\": $ignoredCount," : "")
            . "\n \"classMap\": {"
            . $meta;

        $packageFilename = $this->getPackageFilename($id);
        file_put_contents("$this->packageFilePath/$packageFilename.meta", substr($meta, 0, -1) . "\n }\n}");
        file_put_contents("$this->packageFilePath/$packageFilename", $package);
    }


    /**
     * @param string $id
     * @return string
     */
    private function getPackageFilename($id)
    {
        return "$this->version-$id.php";
    }

    /**
     * @param $id
     * @return bool
     */
    private function isPackageActive($id)
    {
        return isset($this->packages[$id]['active']) && $this->packages[$id]['active'] === true;
    }


    /**
     * @param array  $phpCodeLines
     * @param string $className
     * @param string $fileName
     * @return array
     */
    public function normalizeNamespaceForPackage(array $phpCodeLines, $className, $fileName)
    {
        $namespace = 'namespace';
        $namespaceLength = strlen($namespace);
        $hasNamespace = false;
        $closeNamespace = false;
        $lastUseIndex = null;
        foreach ($phpCodeLines as $i => $line) {
            $trimmedLine = trim($line);
            // remove opening and closing tag lines
            if ($trimmedLine === '<?php' || $trimmedLine === '?>') {
                unset($phpCodeLines[$i]);
                continue;
            }
            // rebuild namespace with ; to namespace with {}
            if (substr($trimmedLine, 0, $namespaceLength) === $namespace) {
                $hasNamespace = true;
                // if not the "namespace xyz;" notation is used, the file itself does the right formatting!
                // when used: rebuild with {}
                if (substr($trimmedLine, -1) === ';') {
                    // information: use CAN be placed within {}!
                    $phpCodeLines[$i] = substr($trimmedLine, 0, -1) . '{';
                    $closeNamespace = true;
                }
                $lastUseIndex = $i;
            }

            if (substr($trimmedLine, 0, 4) === 'use ') {
                $lastUseIndex = $i;
            }
        }

        $classCheck = "if (!class_exists('$className', false)) {";

        $package = "//start of file: '$fileName'\n";

        if ($lastUseIndex !== null) {
            // there was an use or a namespace line -> append check as new line ofter that
            $phpCodeLines[$lastUseIndex] .= "\n" . $classCheck;
        }

        if (!$hasNamespace) {
            $package .= "namespace {\n";
            if ($lastUseIndex === null) {
                $package .= "$classCheck\n";
            }
            $closeNamespace = true;
        }
        $package .= implode("\n", $phpCodeLines) . "\n";

        // attention: the "order" of the closing brackets is irrelevant! we have only to match the number of opened brackets
        // close class check
        $package .= "}\n";
        if ($closeNamespace) {
            // close namespace
            $package .= "}\n";
        }

        $package .= "//end of file: '$fileName'\n";

        return $package;
    }


    /**
     * @param $className
     * @return bool
     */
    private function isClassNameIgnoredForPackage($className)
    {
        foreach ($this->ignores as $ignore) {
            if (substr($className, 0, strlen($ignore)) === $ignore) {
                return true;
            }
        }
        return false;
    }


    /**
     * @param array $ignore
     */
    public function ignorePackageHandlingForClassesStartingWith($ignore = array())
    {
        if (is_string($ignore)) {
            $this->ignores = array($ignore);
        }
        else {
            $this->ignores = $ignore;
        }
    }
}
