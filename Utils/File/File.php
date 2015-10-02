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
    /** @var SplFileObject */
    private $fileObject = null;


    /**
     * @param SplFileObject $fileObject
     */
    public function __construct(SplFileObject $fileObject)
    {
        $this->fileObject = $fileObject;
    }


    /**
     * @param int      $offset
     * @param int|null $limit
     * @return string
     */
    public function getContent($offset = 0, $limit = null)
    {
        $this->fileObject->rewind();
        $fileContent = '';

        $current = 0;
        // TODO: support negative limit?
        $stop = $limit === null ? PHP_INT_MAX : $offset + $limit;

        while (!$this->fileObject->eof() && $current < $stop) {
            $line = $this->fileObject->fgets();
            $current += strlen($line);
            $fileContent .= $line;
            $this->fileObject->next();
        }

        if ($limit === null) {
            // WTF: third parameter = null is not the same like without third parameter
            return substr($fileContent, $offset);
        }

        return substr($fileContent, $offset, $limit);
    }


    /**
     * @return string
     */
    public function getRealPath()
    {
        return $this->fileObject->getRealPath();
    }


    public function isPhp()
    {
        return $this->getContent(0, 5) === '<?php';
    }
}
