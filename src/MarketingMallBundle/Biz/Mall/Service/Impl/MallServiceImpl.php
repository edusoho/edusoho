<?php

namespace MarketingMallBundle\Biz\Mall\Service\Impl;

use Biz\BaseService;
use Biz\CloudPlatform\Service\EduCloudService;
use Biz\Crontab\SystemCrontabInitializer;
use Biz\System\Service\SettingService;
use Codeages\Biz\Framework\Scheduler\Service\SchedulerService;
use Firebase\JWT\JWT;
use MarketingMallBundle\Biz\Mall\Service\MallService;
use MarketingMallBundle\Biz\MallAdminProfile\Service\MallAdminProfileService;
use MarketingMallBundle\Client\MarketingMallApi;

class MallServiceImpl extends BaseService implements MallService
{
    public function isShow()
    {
        // 商城内测
        $alphaTest = $this->getSetting('magic.enable_marketing_mall_alpha_test', 0);

        $canSchoolShowMall = $this->getSetting('cloud_status.accessCloud', false) && !$this->getSetting('developer.without_network', false) && $this->getEduCloudService()->isSaaS();

        return $canSchoolShowMall &&
            $alphaTest &&
            $this->getCurrentUser()->hasPermission('admin_v2_marketing_mall');
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

        $this->getSchedulerService()->register(array(
            'name' => 'MarketingMallSyncListJob',
            'source' => SystemCrontabInitializer::SOURCE_SYSTEM,
            'expression' => '* * * * *',
            'misfire_policy' => 'executing',
            'class' => 'MarketingMallBundle\Biz\SyncList\Job\SyncListJob'
        ));

        $this->dispatchEvent('marketing_mall.init', []);

        return $setting;
    }

    public function readIntroduce()
    {
        $this->getMallAdminProfileService()->setMallAdminProfile($this->getCurrentUser()->getId(), 'introduce_read', 1);
    }

    public function isIntroduceRead()
    {
        $profile = $this->getMallAdminProfileService()->getMallAdminProfileByUserIdAndFieldName($this->getCurrentUser()->getId(), 'introduce_read');

        return !empty($profile);
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

    /**
     * @return MallAdminProfileService
     */
    protected function getMallAdminProfileService()
    {
        return $this->createService('MallAdminProfile:MallAdminProfileService');
    }

    /**
     * @return SchedulerService
     */
    private function getSchedulerService()
    {
        return $this->createService('Scheduler:SchedulerService');
    }
}
