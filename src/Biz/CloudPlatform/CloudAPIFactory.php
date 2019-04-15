<?php

namespace Biz\CloudPlatform;

use Biz\CloudPlatform\Client\AbstractCloudAPI;
use Biz\CloudPlatform\Client\CloudAPI;
use Biz\CloudPlatform\Client\EventCloudAPI;
use Biz\CloudPlatform\Client\FailoverCloudAPI;
use Biz\System\Service\SettingService;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Topxia\Service\Common\ServiceKernel;

class CloudAPIFactory
{
    private static $api;  //单元测试用

    public static function create($type = 'root', $apiVersion = AbstractCloudAPI::DEFAULT_API_VERSION)
    {
        if (empty(self::$api)) {
            /**
             * @var SettingService
             */
            $setting = ServiceKernel::instance()->getBiz()->service('System:SettingService');

            $storage = $setting->get('storage', array());
            $developer = $setting->get('developer', array());

            $logger = new Logger('CloudAPI');
            $logger->pushHandler(new StreamHandler(ServiceKernel::instance()->getParameter('kernel.logs_dir').'/cloud-api.log', Logger::DEBUG));

            if ('tui' == $type) {
                $api = new CloudAPI(array(
                    'accessKey' => empty($storage['cloud_access_key']) ? '' : $storage['cloud_access_key'],
                    'secretKey' => empty($storage['cloud_secret_key']) ? '' : $storage['cloud_secret_key'],
                    'apiUrl' => empty($storage['cloud_api_tui_server']) ? 'http://estui.edusoho.net' : $storage['cloud_api_tui_server'],
                    'debug' => empty($developer['debug']) ? false : true,
                ));
                $api->setLogger($logger);
            } elseif ('event' == $type) {
                $api = new EventCloudAPI(array(
                    'accessKey' => empty($storage['cloud_access_key']) ? '' : $storage['cloud_access_key'],
                    'secretKey' => empty($storage['cloud_secret_key']) ? '' : $storage['cloud_secret_key'],
                    'apiUrl' => empty($storage['cloud_api_event_server']) ? 'http://event.edusoho.net' : $storage['cloud_api_event_server'],
                    'debug' => empty($developer['debug']) ? false : true,
                ));
                $api->setLogger($logger);
            } else {
                if (empty($developer['private_cloud_mode'])) {
                    $api = new FailoverCloudAPI(array(
                        'accessKey' => empty($storage['cloud_access_key']) ? '' : $storage['cloud_access_key'],
                        'secretKey' => empty($storage['cloud_secret_key']) ? '' : $storage['cloud_secret_key'],
                        'apiUrl' => empty($storage['cloud_api_server']) ? '' : $storage['cloud_api_server'],
                        'debug' => empty($developer['debug']) ? false : true,
                        'apiVersion' => $apiVersion,
                    ));
                    $api->setLogger($logger);

                    $serverConfigFile = ServiceKernel::instance()->getParameter('kernel.root_dir').'/data/api_server.json';
                    $api->setApiServerConfigPath($serverConfigFile);
                    $api->setApiType($type);
                } else {
                    $api = new CloudAPI(array(
                        'accessKey' => empty($storage['cloud_access_key']) ? '' : $storage['cloud_access_key'],
                        'secretKey' => empty($storage['cloud_secret_key']) ? '' : $storage['cloud_secret_key'],
                        'apiUrl' => empty($storage['cloud_api_server']) ? '' : $storage['cloud_api_server'],
                        'debug' => empty($developer['debug']) ? false : true,
                        'apiVersion' => $apiVersion,
                    ));
                    $api->setLogger($logger);
                }
            }

            return $api;
        }

        return self::$api;
    }
}
