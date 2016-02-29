<?php

namespace MaterialLib\MaterialLibBundle\Controller;

use Topxia\WebBundle\Controller\BaseController as Base;

class BaseController extends Base
{
    protected function createService($service)
    {
        return $this->getServiceKernel()->createService($service);
    }
}
