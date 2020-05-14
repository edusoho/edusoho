<?php

namespace Biz\S2B2C\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use Biz\DatabaseConfig\Service\ConfigService;
use Biz\S2B2C\Service\CoopModeService;
use Biz\S2B2C\Service\MerchantSettingService;
use Biz\S2B2C\Service\SupplierNotifyService;
use Biz\System\Service\SettingService;

class SupplierNotifyServiceImpl extends BaseService implements SupplierNotifyService
{
    public function onSiteStatusChange($params)
    {
        if (!ArrayToolkit::requireds($params, ['site_id', 'site_status'])) {
            throw new \InvalidArgumentException('Params Missing');
        }

        $this->getDatabaseConfigService()->updateSiteStatusBySiteId($params['site_id'], $params['site_status']);

        return ['success' => true];
    }

    public function onCoopModeChange($params)
    {
        $new = $this->getS2B2CServiceApi()->getMe();
        if (!isset($new['coop_mode'])) {
            $this->getLogger()->error('[onCoopModeChange] 获取渠道商信息失败，停止处理[DATA]：'.json_encode($new));

            return ['status' => false];
        }

        $merchantSetting = $this->getSettingService()->get('merchant_setting', []);
        $this->getSettingService()->set('merchant_setting', array_merge($merchantSetting, [
            'coop_mode' => $new['coop_mode'],
        ]));
        $this->getLogger()->info("[onCoopModeChange] 更新渠道商#{$new['name']}合作模式#{$new['coop_mode']}成功");

        return ['success' => true];
    }

    public function onMerchantDomainUrlChange($params)
    {
        $siteSetting = $this->getSettingService()->get('site');
        $siteSetting['url'] = $params['domain_url'];
        $this->getSettingService()->set('site', $siteSetting);

        $this->getDatabaseConfigService()->updateSiteUrlBySiteId($params['site_id'], $params['domain_url']);
    }

    public function onSupplierDomainUrlChange($params)
    {
        $supplierSetting = $this->getSettingService()->get('supplierSettings');
        $supplierSetting['domainUrl'] = $params['domain_url'];
        $this->getSettingService()->set('supplierSettings', $supplierSetting);
    }

    public function onSupplierSiteLogoChange($params)
    {
        $site = $this->getSettingService()->get('site');

        if (!empty($site['logo'])) {
            $logoData = explode('?', $site['logo']);
            $site['logo'] = $logoData[0].'?'.time();
        }

        if (!empty($site['favicon'])) {
            $faviconData = explode('?', $site['favicon']);
            $site['favicon'] = $faviconData[0].'?'.time();
        }

        $this->getSettingService()->set('site', $site);

        return ['success' => true];
    }

    public function onMerchantAuthNodeChange($params)
    {
        $me = $this->getS2B2CServiceApi()->getMe();
        $authNode = isset($me['auth_node']) ? $me['auth_node'] : [];
        $this->checkAuthNode($authNode);

        $setting = $this->getSettingService()->get('merchant_setting');
        $setting['auth_node'] = [
            'logo' => $me['auth_node']['logo'],
            'title' => $me['auth_node']['title'],
            'favicon' => $me['auth_node']['favicon'],
        ];
        $this->getSettingService()->set('merchant_setting', $setting);

        if (!$me['auth_node']['logo']) {
            $this->getMerchantSettingService()->resetSiteLogo();
        }

        if (!$me['auth_node']['title']) {
            $this->getMerchantSettingService()->resetSiteTitle();
        }

        if (!$me['auth_node']['favicon']) {
            $this->getMerchantSettingService()->resetSiteFavicon();
        }

        return ['success' => true];
    }

    protected function checkAuthNode($authNode)
    {
        $whole = isset($authNode['logo']) &&
                 isset($authNode['title']) &&
                 isset($authNode['favicon']);

        if (!$whole) {
            $this->getLogger()->error('[onMerchantAuthNodeChange] 获取渠道商权限节点失败，停止处理[DATA]：'.json_encode($authNode));
        }
    }

    /**
     * @return ConfigService
     */
    protected function getDatabaseConfigService()
    {
        return $this->createService('DatabaseConfig:ConfigService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    /**
     * @return \QiQiuYun\SDK\Service\S2B2CService
     */
    protected function getS2B2CServiceApi()
    {
        return $this->biz->offsetGet('qiQiuYunSdk.s2b2cService');
    }

    /**
     * @return mixed|\Monolog\Logger
     */
    protected function getLogger()
    {
        return $this->biz->offsetGet('s2b2c.merchant.logger');
    }

    /**
     * @return CoopModeService
     */
    protected function getCoopModeService()
    {
        return $this->createService('S2B2C:CoopModeService');
    }

    /**
     * @return MerchantSettingService
     */
    protected function getMerchantSettingService()
    {
        return $this->createService('S2B2C:MerchantSettingService');
    }
}
