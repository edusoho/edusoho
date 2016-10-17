<?php
namespace Topxia\Service\CloudPlatform;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Topxia\Service\Common\ServiceKernel;
use Codeages\RestApiClient\RestApiClient;
use Codeages\RestApiClient\Specification\JsonHmacSpecification;

class IMAPIFactory
{
    private static $client;

    public static function create()
    {
        if (!empty(self::$client)) {
            return self::$client;
        }

        $setting = ServiceKernel::instance()->createService('System.SettingService');

        $storage   = $setting->get('storage', array());
        $developer = $setting->get('developer', array());

        $logger = new Logger('IM');
        $logger->pushHandler(new StreamHandler(ServiceKernel::instance()->getParameter('kernel.logs_dir').'/cloud-api.log', Logger::DEBUG));

        $config = array(
            'accessKey' => empty($storage['cloud_access_key']) ? '' : $storage['cloud_access_key'],
            'secretKey' => empty($storage['cloud_secret_key']) ? '' : $storage['cloud_secret_key'],
            'endpoint'    => empty($storage['cloud_api_im_server']) ? 'http://imapi.edusoho.net/v1/' : $storage['cloud_api_im_server'],
        );

        $spec = new JsonHmacSpecification('sha1');
        $client = new RestApiClient($config, $spec, null, $logger, empty($developer['debug']) ? false : true);

        self::$client = $client;

        return $client;
    }
}
