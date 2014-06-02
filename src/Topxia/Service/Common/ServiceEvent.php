<?php
namespace Topxia\Service\Common;

use Symfony\Component\EventDispatcher\GenericEvent;
use Topxia\Service\Common\ServiceKernel;

class ServiceEvent extends GenericEvent
{

    protected function getKernel()
    {
        return ServiceKernel::instance();
    }

    protected function createService($name)
    {
        return $this->getKernel()->createService($name);
    }

    public function getCurrentUser()
    {
        return $this->getKernel()->getCurrentUser();
    }

}