<?php
namespace Topxia\Service\CloudPlatform;

use Topxia\Service\Common\ServiceKernel;
use Topxia\Service\CloudPlatform\Client\CloudAPI;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class CloudAPIFactory 
{

    public static function create()
    {
        $setting = ServiceKernel::instance()->createService('System.SettingService');

        $storage = $setting->get('storage', array());
        $developer = $setting->get('developer', array());

        $api = new CloudAPI(array(
            'accessKey' => empty($storage['cloud_access_key']) ? '' : $storage['cloud_access_key'],
            'secretKey' => empty($storage['cloud_secret_key']) ? '' : $storage['cloud_secret_key'],
            'apiUrl' => empty($storage['cloud_api_server']) ? '' : $storage['cloud_api_server'],
            'debug' => empty($developer['debug']) ? false : true,
        ));

        $logger = new Logger('CloudAPI');
        $logger->pushHandler(new StreamHandler(ServiceKernel::instance()->getParameter('kernel.logs_dir') . '/cloud-api.log', Logger::DEBUG));
        $api->setLogger($logger);

        return $api;
    }

}