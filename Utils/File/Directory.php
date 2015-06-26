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

use SplFileInfo;

/**
 * Class File
 * @package glady\Behind\Utils\File
 */
class Directory
{
    /** @var SplFileInfo */
    private $fileInfo = null;


    /**
     * @param SplFileInfo $fileInfo
     */
    public function __construct(SplFileInfo $fileInfo)
    {
        $this->fileInfo = $fileInfo;
    }


    /**
     * @return array|File[]|Directory[]
     */
    public function getContent()
    {
        return array();
    }


    /**
     * @return string
     */
    public function getRealPath()
    {
        return $this->fileInfo->getRealPath();
    }
}
