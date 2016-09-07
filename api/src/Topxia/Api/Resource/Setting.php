<?php

namespace Topxia\Api\Resource;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class Setting extends BaseResource
{
    public function get(Application $app, Request $request, $settingName)
    {
        if (!$this->canAccess($settingName)) {
            return array();
        }

        $res = $this->getSettingService()->get($settingName);

        $method = 'filter' . ucfirst($settingName);
        if (method_exists($this, $method)) {
            return call_user_func(array($this, $method), $res);
        } else {
            return array();
        }
    }

    public function filter($res)
    {
        return $res;
    }


    protected function filterCourse($res)
    {
        return $this->filterKeys($res, array('welcome_message_enabled'));
    }

    protected function filterApp_im($res)
    {
        return $this->filterKeys($res, array('welcome_message_enabled'));
    }

    protected function filterKeys(array $input, array $allowed)
    {
        return array_diff_key($input, array_flip($allowed));
    }

    protected function canAccess($settingName)
    {
        return in_array($settingName, array(
            'course',
            'app_im'
        ), true);
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }
}
