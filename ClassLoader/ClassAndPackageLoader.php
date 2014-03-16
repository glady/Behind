<?php

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
                    $me->addClassToPackage($id, $className, $fileName);
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
        $package = "<?php\n";
        foreach ($this->packages[$id]['classMap'] as $className => $fileName) {
            $package .= "if (!class_exists('$className', false)) {\n//start of file: '$fileName' ?>";
            $package .= file_get_contents($fileName);
            $package .= "\n//end of file: '$fileName'\n}\n\n";
        }
        $packageFilename = $this->getPackageFilename($id);
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
} 