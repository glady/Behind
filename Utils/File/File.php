<?php
/*
 * This file is part of the Behind-Project (https://github.com/glady/Behind).
 *
 * (c) Mike Gladysch <mail@mike-gladysch.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace glady\Behind\Utils\File;

use SplFileObject;

/**
 * Class File
 * @package glady\Behind\Utils\File
 */
class File
{
    private $fileObject = null;


    /**
     * @param SplFileObject $fileObject
     */
    public function __construct(SplFileObject $fileObject)
    {
        $this->fileObject = $fileObject;
    }


    public function getContent()
    {
        $this->fileObject->rewind();
        $fileContent = '';
        while (!$this->fileObject->eof()) {
            $fileContent .= $this->fileObject->fgets();
            $this->fileObject->next();
        }

        return $fileContent;
    }


    public function getRealPath()
    {
        return $this->fileObject->getRealPath();
    }
}
