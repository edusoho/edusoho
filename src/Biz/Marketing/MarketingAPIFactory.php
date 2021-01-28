<?php

namespace Biz\Marketing;

use Topxia\Service\Common\ServiceKernel;
use Codeages\RestApiClient\RestApiClient;
use Codeages\RestApiClient\Specification\JsonHmacSpecification2;
use Biz\Marketing\Util\MarketingUtils;

class MarketingAPIFactory
{
    public static function create($endpoint = '/merchant')
    {
        $settingService = ServiceKernel::instance()->getBiz()->service('System:SettingService');
        $storage = $settingService->get('storage', array());
        $developerSetting = $settingService->get('developer', array());

        $marketingDomain = MarketingUtils::getMarketingDomain();

        $config = array(
            'accessKey' => $storage['cloud_access_key'],
            'secretKey' => $storage['cloud_secret_key'],
            'endpoint' => $marketingDomain.$endpoint,
        );
        $spec = new JsonHmacSpecification2('sha1');
        $client = new RestApiClient($config, $spec);

        return $client;
    }
}
