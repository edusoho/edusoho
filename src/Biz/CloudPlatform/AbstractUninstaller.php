<?php

namespace Biz\CloudPlatform;

use Topxia\Service\Common\ServiceKernel;

abstract class AbstractUninstaller
{
    /**
     * @var ServiceKernel
     */
    protected $kernel;

    public function __construct(ServiceKernel $kernel)
    {
        $this->kernel = $kernel;
    }

    public function getConnection()
    {
        return $this->kernel->getConnection();
    }

    protected function createService($name)
    {
        return $this->kernel->createService($name);
    }

    protected function createDao($name)
    {
        return $this->kernel->createDao($name);
    }

    abstract public function uninstall();
}
