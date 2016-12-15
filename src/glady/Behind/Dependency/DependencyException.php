<?php
/*
 * This file is part of the Behind-Project (https://github.com/glady/Behind).
 *
 * (c) Mike Gladysch <mail@mike-gladysch.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace glady\Behind\Dependency;

use Exception;

/**
 * Class DependencyException
 * @package glady\Behind\Dependency
 */
class DependencyException extends Exception
{
    const CODE_UNKNOWN = 0;
    const ERROR_UNKNOWN = 'unknown';

    const CODE_NOT_CONFIGURED = 1;
    const ERROR_NOT_CONFIGURED = 'not_configured';

    /**
     * all possible "predefined" exceptions.
     * @var array
     */
    private static $errorDescriptions = array(
        self::ERROR_NOT_CONFIGURED => array(
            'code' => self::CODE_NOT_CONFIGURED,
            'message' => 'DependencyException - dependency %dependencyName% was requested, but not configured.'
        ),
        self::ERROR_UNKNOWN => array(
            'code' => self::CODE_UNKNOWN,
            'message' => 'Unknown DependencyException - nothing defined for this exception'
        )
    );


    /**
     * @param string $exceptionId
     * @param array  $params
     * @return DependencyException
     */
    public static function create($exceptionId = self::ERROR_UNKNOWN, array $params = array())
    {
        return new self(
            self::buildExceptionMessage($exceptionId, $params),
            self::getErrorCode($exceptionId),
            null
        );
    }


    /**
     * @param string $exceptionId
     * @param array $params
     * @return mixed
     */
    private static function buildExceptionMessage($exceptionId = self::ERROR_UNKNOWN, array $params = array())
    {
        $message = isset(self::$errorDescriptions[$exceptionId]['message'])
            ? self::$errorDescriptions[$exceptionId]['message']
            : self::$errorDescriptions[self::ERROR_UNKNOWN]['message'];

        if (empty($params)) {
            return $message;
        }

        $search = array();
        $replace = array();
        foreach ($params as $key => $value) {
            $search[] = "%$key%";
            $replace[] = "'$value'";
        }

        return str_replace($search, $replace, $message);
    }


    /**
     * @param string $exceptionId
     * @return int
     */
    private static function getErrorCode($exceptionId = self::ERROR_UNKNOWN)
    {
        return isset(self::$errorDescriptions[$exceptionId]['code'])
            ? self::$errorDescriptions[$exceptionId]['code']
            : self::$errorDescriptions[self::ERROR_UNKNOWN]['code'];
    }
}
