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
            'apiServer' => $setting['cloud_api_server'],
            'accessKey' => $setting['cloud_access_key'],
            'secretKey' => $setting['cloud_secret_key'],
            'bucket' => $setting['cloud_bucket'],
        ));

        $client = new $class($arguments);

        return $client;
	}

	private function getKernenl()
	{
		return ServiceKernel::instance();
	}

}