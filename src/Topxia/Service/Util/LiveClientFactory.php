<?php

namespace Topxia\Service\Util;

use Topxia\Service\Common\ServiceKernel;

class LiveClientFactory
{

    public static function createClient()
    {
        $setting = ServiceKernel::instance()->createService('System.SettingService')->get('storage', array());

        $class = __NAMESPACE__ . '\\EdusohoLiveClient';

        $arguments = array(
            'apiServer' => $setting['cloud_api_server'],
            'accessKey' => $setting['cloud_access_key'],
            'secretKey' => $setting['cloud_secret_key'],
        );

        $client = new $class($arguments);

        return $client;
    }

}