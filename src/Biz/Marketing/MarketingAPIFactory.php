<?php

namespace Biz\Marketing;

use Topxia\Service\Common\ServiceKernel;
use Codeages\RestApiClient\RestApiClient;
use Codeages\RestApiClient\Specification\JsonHmacSpecification2;

class MarketingAPIFactory
{
    public static function create()
    {
        $settingService = ServiceKernel::instance()->getBiz()->service('System:SettingService');
        $storage = $settingService->get('storage', array());
        $developerSetting = $settingService->get('developer', array());

        $marketingDomain = !empty($developerSetting['marketing_domain']) ? $developerSetting['marketing_domain'] : 'http://wyx.edusoho.cn';

        $config = array(
            'accessKey' => $storage['cloud_access_key'],
            'secretKey' => $storage['cloud_secret_key'],
            'endpoint' => $marketingDomain.'/merchant',
        );
        $spec = new JsonHmacSpecification2('sha1');
        $client = new RestApiClient($config, $spec);

        return $client;
    }
}
