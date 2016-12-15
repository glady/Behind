<?php
/*
 * This file is part of the Behind-Project (https://github.com/glady/Behind).
 *
 * (c) Mike Gladysch <mail@mike-gladysch.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace glady\Behind\Dependency\test\example\ExampleClasses;

/**
 * Class Request
 * @package glady\Behind\Dependency\test\example\ExampleClasses
 */
class Request
{

    const CLASS_NAME = __CLASS__;

    private $getParams;
    private $postParams;
    private $headers;
    private $cookies;


    public function setGetParams(array $getParams)
    {
        $this->getParams = $getParams;
    }


    public function setPostParams(array $postParams)
    {
        $this->postParams = $postParams;
    }


    public function setHeaders(array $headers)
    {
        $this->headers = $headers;
    }


    public function setCookies(array $cookies)
    {
        $this->cookies = $cookies;
    }


    public function getRequestParam($name)
    {
        return $this->postParams[$name]
            ?? $this->getParams[$name]
            ?? null;
    }
}
