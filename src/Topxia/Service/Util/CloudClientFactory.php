<?php

namespace Topxia\Service\Util;

use Biz\System\Service\SettingService;
use Topxia\Service\Common\ServiceKernel;

class CloudClientFactory
{
    public function createClient()
    {
        $parameter = $this->getKernenl()->getParameter('cloud_client');

        $arguments = empty($parameter['arguments']) ? array() : $parameter['arguments'];

        $setting = $this->getSettingService()->get('storage', array());

        $class = __NAMESPACE__.'\\EdusohoCloudClient';

        $arguments = array_merge($arguments, array(
            'apiServer' => empty($setting['cloud_api_server']) ? '' : $setting['cloud_api_server'],
            'accessKey' => empty($setting['cloud_access_key']) ? '' : $setting['cloud_access_key'],
            'secretKey' => empty($setting['cloud_secret_key']) ? '' : $setting['cloud_secret_key']
        ));

        $client = new $class($arguments);

        return $client;
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return ServiceKernel::instance()->getBiz()->service('System:SettingService');
    }

    protected function getKernenl()
    {
        return ServiceKernel::instance();
    }

}
