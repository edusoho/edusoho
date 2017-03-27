<?php
namespace Topxia\Service\Common;


use Codeages\Biz\Framework\Event\Event;

class ServiceEvent extends Event
{

    public function getKernel()
    {
        return ServiceKernel::instance();
    }

    public function createService($name)
    {
        return $this->getKernel()->createService($name);
    }

    public function getCurrentUser()
    {
        return $this->getKernel()->getCurrentUser();
    }

}