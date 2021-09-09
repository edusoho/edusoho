<?php

namespace Biz\WeChat\Service\Impl;

use Biz\BaseService;
use Biz\WeChat\Service\WeChatAppService;

class WeChatAppServiceImpl extends BaseService implements WeChatAppService
{
    public function getWeChatAppStatus()
    {
        $wechatApp = null;
        $installedWechatApp = null;

        $apps = $this->getAppService()->getCenterApps();
        foreach ($apps as $app) {
            if (0 == strcasecmp('WeChatApp', $app['code'])) {
                $wechatApp = $app;
                break;
            }
        }

        if (!empty($wechatApp)) {
            $installedApps = $this->getAppService()->findApps(0, 100);
            foreach ($installedApps as $installedApp) {
                if (0 == strcasecmp('WeChatApp', $installedApp['code'])) {
                    $installedWechatApp = $installedApp;
                    break;
                }
            }
        }

        $wechatApp = $this->getSettingService()->get('wechat_app', []);

        return [
            'latestPackageId' => $wechatApp['latestPackageId'],
            'purchased' => $wechatApp['purchased'],
            'installed' => !empty($installedWechatApp),
            'configured' => !empty($wechatApp['appid']) && !empty($wechatApp['secret']),
        ];
    }

    /**
     * @return AppService
     */
    protected function getAppService()
    {
        return $this->createService('CloudPlatform:AppService');
    }

    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }
}
