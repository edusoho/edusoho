<?php

namespace Biz;

use AppBundle\System;
use Codeages\Biz\Framework\Context\Biz;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

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

        $biz['qiQiuYunSdk.playv2'] = function ($biz) use ($that) {
            $service = null;
            $sdk = $that->generateSdk($biz, $that->getPlayV2Config($biz));
            if (!empty($sdk)) {
                $service = $sdk->getPlayV2Service();
            }

            return $service;
        };

        $biz['qiQiuYunSdk.esOp'] = function ($biz) use ($that) {
            $service = null;
            $sdk = $that->generateSdk($biz, $that->getESopConfig($biz));
            if (!empty($sdk)) {
                $service = $sdk->getESopService();
            }

            return $service;
        };

        $biz['qiQiuYunSdk.mp'] = function ($biz) use ($that) {
            $service = null;
            $sdk = $that->generateSdk($biz, $that->getMpConfig($biz));
            if (!empty($sdk)) {
                $service = $sdk->getMpService();
            }

            return $service;
        };

        $biz['qiQiuYunSdk.aiface'] = function ($biz) use ($that) {
            $service = null;
            $sdk = $that->generateSdk($biz, $that->getAIFaceConfig($biz));
            if (!empty($sdk)) {
                $service = $sdk->getAiService();
            }

            return $service;
        };

        $biz['qiQiuYunSdk.push'] = function ($biz) use ($that) {
            $service = null;
            $sdk = $that->generateSdk($biz, $that->getPushConfig($biz));
            if (!empty($sdk)) {
                $service = $sdk->getPushService();
            }

            return $service;
        };

        /*
         * @param $biz
         * @return \QiQiuYun\SDK\Service\NotificationService|null
         */
        $biz['qiQiuYunSdk.notification'] = function ($biz) use ($that) {
            $service = null;

            $sdk = $that->generateSdk($biz, $that->getNotificationConfig($biz));
            if (!empty($sdk)) {
                $service = $sdk->getNotificationService();
            }

            return $service;
        };

        $biz['qiQiuYunSdk.sms'] = function ($biz) use ($that) {
            $service = null;

            $sdk = $that->generateSdk($biz, array());
            if (!empty($sdk)) {
                $service = $sdk->getSmsService();
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

    public function getESopConfig($biz)
    {
        $setting = $biz->service('System:SettingService');
        $developerSetting = $setting->get('developer', array());

        if (!empty($developerSetting['cloud_api_es_op_server'])) {
            $urlSegs = explode('://', $developerSetting['cloud_api_es_op_server']);
            if (2 == count($urlSegs)) {
                $hostUrl = $urlSegs[1];
            }
        }

        if (empty($hostUrl)) {
            $hostUrl = '';
        }

        return array('esop' => array('host' => $hostUrl));
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

    public function getMpConfig(Biz $biz)
    {
        $setting = $biz->service('System:SettingService');
        $developerSetting = $setting->get('developer', array());
        if (isset($developerSetting['mp_service_url']) && !empty($developerSetting['mp_service_url'])) {
            $urlSegs = explode('://', $developerSetting['mp_service_url']);
            if (2 == count($urlSegs)) {
                $hostUrl = $urlSegs[1];
            }
        }
        if (empty($hostUrl)) {
            $hostUrl = '';
        }

        return array('mp' => array('host' => $hostUrl));
    }

    public function getAIFaceConfig(Biz $biz)
    {
        $setting = $biz->service('System:SettingService');
        $developerSetting = $setting->get('developer', array());

        if (isset($developerSetting['ai_face_url']) && !empty($developerSetting['ai_face_url'])) {
            $urlSegs = explode('://', $developerSetting['ai_face_url']);
            if (2 == count($urlSegs)) {
                $hostUrl = $urlSegs[1];
            }
        }
        if (empty($hostUrl)) {
            $hostUrl = '';
        }

        return array('ai' => array('host' => $hostUrl));
    }

    public function getPushConfig(Biz $biz)
    {
        $setting = $biz->service('System:SettingService');
        $developerSetting = $setting->get('developer', array());

        if (isset($developerSetting['push_url']) && !empty($developerSetting['push_url'])) {
            $urlSegs = explode('://', $developerSetting['push_url']);
            if (2 == count($urlSegs)) {
                $hostUrl = $urlSegs[1];
            }
        }
        if (empty($hostUrl)) {
            $hostUrl = '';
        }

        return array('push' => array('host' => $hostUrl));
    }

    public function getPlayV2Config($biz)
    {
        $setting = $biz->service('System:SettingService');
        $developerSetting = $setting->get('developer', array());

        if (empty($developerSetting['cloud_play_server'])) {
            return array();
        }

        $url = parse_url($developerSetting['cloud_play_server']);

        if (empty($url['host'])) {
            return array();
        }

        return array('playv2' => array('host' => $url['host']));
    }

    public function getNotificationConfig($biz)
    {
        $setting = $biz->service('System:SettingService');
        $developerSetting = $setting->get('developer', array());

        if (empty($developerSetting['cloud_api_notification_server'])) {
            return array();
        }

        $url = parse_url($developerSetting['cloud_api_notification_server']);

        if (empty($url['host'])) {
            return array();
        }

        return array('notification' => array('host' => $url['host']));
    }
}
