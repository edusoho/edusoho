<?php

namespace MaterialLib\MaterialLibBundle\Controller;

use Topxia\WebBundle\Controller\BaseController as Base;

class BaseController extends Base
{
    protected function getUploadFileService()
    {
        return $this->createService('File.UploadFileService2');
    }

    protected function createService($service)
    {
        return $this->getServiceKernel()->createService($service);
    }
}
