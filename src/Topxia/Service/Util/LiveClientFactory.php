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
            'apiUrl' => empty($setting['cloud_api_server']) ? 'http://api.edusoho.net' : $setting['cloud_api_server'],
            'accessKey' => empty($setting['cloud_access_key']) ? '' : $setting['cloud_access_key'],
            'secretKey' => empty($setting['cloud_secret_key']) ? '' : $setting['cloud_secret_key'],
        );

        $client = new $class($arguments);

        return $client;
    }

}