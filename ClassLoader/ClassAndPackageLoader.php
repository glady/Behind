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
        if ($this->hasStoredPackage($id)) {
            $this->includeStoredPackage($id);
        }
        else {
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
        if ($this->isPackageActive($id)) {
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
        if (file_exists("$this->packageFilePath/$packageFilename")) {
            include "$this->packageFilePath/$packageFilename";
        }
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
        $ns = 'namespace';
        $nsLength = strlen($ns);
        $hasNs = false;
        $closeNs = false;
        $lastUseIndex = null;
        foreach ($phpCodeLines as $i => $line) {
            $trimmedLine = trim($line);
            // remove opening and closing tag lines
            if ($trimmedLine === '<?php' || $trimmedLine === '?>') {
                unset($phpCodeLines[$i]);
                continue;
            }
            // rebuild namespace with ; to namespace with {}
            if (substr($trimmedLine, 0, $nsLength) === $ns) {
                $hasNs = true;
                // if not the "namespace xyz;" notation is used, the file itself does the right formatting!
                // when used: rebuild with {}
                if (substr($trimmedLine, -1) === ';') {
                    // information: use CAN be placed within {}!
                    $phpCodeLines[$i] = substr($trimmedLine, 0, -1) . '{';
                    $closeNs = true;
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

        if (!$hasNs) {
            $package .= "namespace {\n";
            if ($lastUseIndex === null) {
                $package .= "$classCheck\n";
            }
            $closeNs = true;
        }
        $package .= implode("\n", $phpCodeLines) . "\n";

        // attention: the "order" of the closing brackets is irrelevant! we have only to match the number of opened brackets
        // close class check
        $package .= "}\n";
        if ($closeNs) {
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