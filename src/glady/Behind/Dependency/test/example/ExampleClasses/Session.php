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
 * Class Session
 * @package glady\Behind\Dependency\test\example\ExampleClasses
 */
class Session
{

    const CLASS_NAME = __CLASS__;

    /** @var SessionDependencyInterface */
    private $dependency;


    /**
     * Session constructor.
     * @param SessionDependencyInterface $sessionDependency
     */
    public function __construct(SessionDependencyInterface $sessionDependency)
    {
        $this->dependency = $sessionDependency;
    }


    /**
     * starts session
     */
    public function start()
    {
        $someRequestParam = $this->dependency->getSomeRequestParam();
        echo "Session with someRequestParam '$someRequestParam' started!\n";
    }
}
