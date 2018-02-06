<?php

namespace Biz;

use Pimple\ServiceProviderInterface;
use Pimple\Container;

class DefaultSdkProvider implements ServiceProviderInterface
{
    public function register(Container $biz)
    {
        $biz['qiQiuYunSdk.drp'] = function ($biz) {
            $service = null;
            $sdk = $this->generateSdk($biz, $this->getDrpConfig($biz));
            if (!empty($sdk)) {
                $service = $sdk->getDrpService();
            }

            return $service;
        };
    }

    private function generateSdk($biz, $serviceConfig)
    {
        $setting = $biz->service('System:SettingService');

        $storageSetting = $setting->get('storage', array());

        $sdk = null;
        if (!empty($storageSetting['cloud_access_key']) && !empty($storageSetting['cloud_secret_key'])) {
            $sdk = new \QiQiuYun\SDK\QiQiuYunSDK(
                array(
                    'access_key' => $storageSetting['cloud_access_key'],
                    'secret_key' => $storageSetting['cloud_secret_key'],
                    'service' => $serviceConfig,
                )
            );
        }

        return $sdk;
    }

    private function getDrpConfig($biz)
    {
        $setting = $biz->service('System:SettingService');
        $developerSetting = $setting->get('developer', array());

        if (!empty($developerSetting['distributor_server'])) {
            $urlSegs = explode('://', $developerSetting['distributor_server']);
            if (2 == count($urlSegs)) {
                $hostUrl = $urlSegs[1];
            }
        }

        if (empty($hostUrl)) {
            $hostUrl = '';
        }

        return array('drp' => array('host' => $hostUrl));
    }
}
