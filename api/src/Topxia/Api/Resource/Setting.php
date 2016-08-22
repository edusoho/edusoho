<?php

namespace Topxia\Api\Resource;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class Setting extends BaseResource
{
    public function get(Application $app, Request $request, $settingName)
    {
        return $this->getSettingService()->get($settingName);
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }
}
