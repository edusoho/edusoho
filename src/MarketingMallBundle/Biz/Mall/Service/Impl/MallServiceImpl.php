<?php

namespace MarketingMallBundle\Biz\Mall\Service\Impl;

use Biz\BaseService;
use Biz\CloudPlatform\Service\EduCloudService;
use Biz\System\Service\SettingService;
use Firebase\JWT\JWT;
use MarketingMallBundle\Biz\Mall\Service\MallService;
use MarketingMallBundle\Client\MarketingMallApi;

class MallServiceImpl extends BaseService implements MallService
{
    public function isShow()
    {
        return $this->getSetting('cloud_status.accessCloud', false) && !$this->getSetting('developer.without_network', false) && $this->getEduCloudService()->isSaaS();
    }

    public function isInit()
    {
        $mallSetting = $this->getSettingService()->get('marketing_mall', []);

        return !empty($mallSetting['access_key']);
    }

    public function init($userInfo, $url)
    {
        $storage = $this->getSettingService()->get('storage', []);

        $client = new MarketingMallApi($storage);
        $authorization = JWT::encode(['exp' => time() + 1000 * 3600 * 24, 'userInfo' => $userInfo, 'access_key' => $storage['cloud_access_key'], 'header' => 'MARKETING_MALL'], $storage['cloud_secret_key']);
        $result = $client->init([
            'token' => $authorization,
            'url' => $url,
            'code' => $storage['cloud_access_key'],
        ]);
        $setting = [
            'access_key' => $result['accessKey'],
            'secret_key' => $result['secretKey'],
            'code' => $result['code'],
        ];
        $this->getSettingService()->set('marketing_mall', $setting);
        $this->dispatchEvent('marketing_mall.init', []);

        return $setting;
    }

    protected function getSetting($name, $default = null)
    {
        return $this->createService('System:SettingService')->node($name, $default);
    }

    /**
     * @return EduCloudService
     */
    protected function getEduCloudService()
    {
        return $this->createService('CloudPlatform:EduCloudService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }
}
