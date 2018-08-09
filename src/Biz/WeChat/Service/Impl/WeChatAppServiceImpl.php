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

        return array(
            'latestPackageId' => $wechatApp['latestPackageId'],
            'purchased' => $wechatApp['purchased'],
            'installed' => !empty($installedWechatApp),
        );
    }

    /**
     * @return AppService
     */
    protected function getAppService()
    {
        return $this->createService('CloudPlatform:AppService');
    }
}
