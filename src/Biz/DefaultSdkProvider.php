<?php

namespace Biz;

use Pimple\ServiceProviderInterface;

class DefaultSdkProvider implements ServiceProviderInterface
{
    public function register(Container $biz)
    {
        $biz['qiQiuYunSdk'] = function ($biz) {
            $setting = $biz->service('System:SettingService');
            $developerSetting = $setting->get('developer', array());
            $settings = $setting->get('storage', array());

            $sdk = new \QiQiuYun\SDK\QiQiuYunSDK(
                array(
                'access_key' => $settings['cloud_access_key'],
                'secret_key' => $settings['cloud_secret_key'],
                'service' => array(
                    'drp' => array(
                        'host' => $developerSetting['distributor_server'],
                    ),
                ), )
            );

            return $sdk;
        };
    }
}
