<?php
namespace Custom\WebBundle\Controller;

class BaseController extends \Topxia\WebBundle\Controller\BaseController
{
    public function checkId($id)
    {
        if ($id <= 0) {
            throw $this->createNotFoundException();
        }
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }
}
