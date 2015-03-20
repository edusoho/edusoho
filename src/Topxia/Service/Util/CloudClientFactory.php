<?php

namespace Topxia\Service\Util;

use Topxia\Service\Common\ServiceKernel;

class CloudClientFactory
{

    public function createClient()
    {
        $parameter = $this->getKernenl()->getParameter('cloud_client');

        $arguments = empty($parameter['arguments']) ? array() : $parameter['arguments'];

        $setting = $this->getKernenl()->createService('System.SettingService')->get('storage', array());

        $class = __NAMESPACE__ . '\\EdusohoCloudClient';

        $arguments = array_merge($arguments, array(
            'apiServer' => empty($setting['cloud_api_server']) ? '' : $setting['cloud_api_server'],
            'accessKey' => empty($setting['cloud_access_key']) ? '' : $setting['cloud_access_key'],
            'secretKey' => empty($setting['cloud_secret_key']) ? '' : $setting['cloud_secret_key'],
            'bucket' =>  empty($setting['cloud_bucket']) ? '' : $setting['cloud_bucket'],
        ));

        $client = new $class($arguments);

        return $client;
    }

    private function getKernenl()
    {
        return ServiceKernel::instance();
    }

}