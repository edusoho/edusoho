<?php


namespace Fomalhaut\AcctSetupBundle\Process\Step;

use Sylius\Bundle\FlowBundle\Process\Step\ControllerStep;
use Topxia\Service\Common\ServiceKernel;

abstract class AbstractStep extends ControllerStep
{
    protected function getServiceKernel()
    {
        return ServiceKernel::instance();
    }

    protected function createService($name)
    {
        return $this->getServiceKernel()->createService($name);
    }

    protected function createDao($name)
    {
        return $this->getServiceKernel()->createDao($name);
    }
} 