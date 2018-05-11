<?php

namespace Biz;

use AppBundle\System;
use Codeages\Biz\Framework\Context\Biz;
use Pimple\ServiceProviderInterface;
use Pimple\Container;

class DefaultSdkProvider implements ServiceProviderInterface
{
    public function register(Container $biz)
    {
        $that = $this;
        $biz['qiQiuYunSdk.drp'] = function ($biz) use ($that) {
            $service = null;
            $sdk = $that->generateSdk($biz, $that->getDrpConfig($biz));
            if (!empty($sdk)) {
                $service = $sdk->getDrpService();
            }

            return $service;
        };

        $biz['qiQiuYunSdk.xapi'] = function ($biz) use ($that) {
            $service = null;
            $sdk = $that->generateSdk($biz, $that->getXAPIConfig($biz));
            if (!empty($sdk)) {
                $service = $sdk->getXAPIService();
            }

            return $service;
        };

        $biz['qiQiuYunSdk.play'] = function ($biz) use ($that) {
            $service = null;
            $sdk = $that->generateSdk($biz, array());
            if (!empty($sdk)) {
                $service = $sdk->getPlayService();
            }

            return $service;
        };

        $biz['qiQiuYunSdk.mp'] = function ($biz) use ($that) {
            $service = null;
            $sdk = $that->generateSdk($biz, array());
            if (!empty($sdk)) {
                $service = $sdk->getMpService();
            }

            return $service;
        };
    }

    public function generateSdk($biz, $serviceConfig)
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

    public function getDrpConfig($biz)
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

    public function getXAPIConfig(Biz $biz)
    {
        $settingService = $biz->service('System:SettingService');
        $siteSettings = $settingService->get('site', array());
        $xapiSetting = $settingService->get('xapi', array());
        $pushUrl = !empty($xapiSetting['push_url']) ? $xapiSetting['push_url'] : 'lrs.qiqiuyun.net/v1/xapi/';
        $pushUrl = ltrim($pushUrl, ' ');
        $pushUrl = rtrim($pushUrl, '/');
        $pushUrl = ltrim($pushUrl, 'http://');
        $pushUrl = ltrim($pushUrl, 'https://');
        $siteName = empty($siteSettings['name']) ? 'none' : $siteSettings['name'];
        $siteUrl = empty($siteSettings['url']) ? '' : $siteSettings['url'];

        return array(
            'xapi' => array(
                'host' => $pushUrl,
                'school_name' => $siteName,
                'school_url' => $siteUrl,
                'school_version' => System::VERSION,
            ),
        );
    }
}
