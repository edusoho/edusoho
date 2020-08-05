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
            $sdk = $that->generateSdk($biz, []);
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

        /*
         * @param $biz
         * @return \QiQiuYun\SDK\Service\WeChatService|null
         */
        $biz['qiQiuYunSdk.wechat'] = function ($biz) use ($that) {
            $service = null;

            $sdk = $that->generateSdk($biz, $that->getWechatConfig($biz));
            if (!empty($sdk)) {
                $service = $sdk->getWeChatService();
            }

            return $service;
        };

        $biz['qiQiuYunSdk.sms'] = function ($biz) use ($that) {
            $service = null;

            $sdk = $that->generateSdk($biz, []);
            if (!empty($sdk)) {
                $service = $sdk->getSmsService();
            }

            return $service;
        };

        $biz['qiQiuYunSdk.platformNews'] = function ($biz) use ($that) {
            $service = null;

            $sdk = $that->generateSdk($biz, $that->getPlatformNewsConfig($biz));

            if (!empty($sdk)) {
                $service = $sdk->getPlatformNewsService();
            }

            return $service;
        };

        /*S2B2C-CUSTOM*/
        $biz['qiQiuYunSdk.s2b2cService'] = function (Biz $biz) use ($that) {
            $service = null;
            // $sdk = $that->generateSdk($biz, array('s2b2c' => array('host' => $host)), $biz->offsetGet('s2b2c.merchant.logger'));
            $sdk = $that->generateSdk($biz, $this->getS2B2CConfig($biz), $biz['s2b2c.merchant.logger']);

            if (!empty($sdk)) {
                $service = $sdk->getS2B2CService();
            }

            return $service;
        };

        $biz['ESCloudSdk.mobile'] = function ($biz) use ($that) {
            $service = null;
            $sdk = $that->generateEsCloudSdk($biz, []);
            if (!empty($sdk)) {
                $service = $sdk->getMobileService();
            }

            return $service;
        };
        /*END*/

        $biz['ESCloudSdk.play'] = function ($biz) use ($that) {
            $service = null;
            $sdk = $that->generateEsCloudSdk($biz, []);
            if (!empty($sdk)) {
                $service = $sdk->getPlayService();
            }

            return $service;
        };

        $biz['ESCloudSdk.resource'] = function ($biz) use ($that) {
            $service = null;
            $sdk = $that->generateEsCloudSdk($biz, []);
            if (!empty($sdk)) {
                $service = $sdk->getResourceService();
            }

            return $service;
        };
    }

    /**
     * @param $biz
     * @param $serviceConfig
     * @param null $logger
     *
     * @return \QiQiuYun\SDK\QiQiuYunSDK|null
     *
     * @throws \QiQiuYun\SDK\Exception\SDKException
     */
    public function generateSdk($biz, $serviceConfig, $logger = null)
    {
        $setting = $biz->service('System:SettingService');

        $storageSetting = $setting->get('storage', []);

        $sdk = null;
        if (!empty($storageSetting['cloud_access_key']) && !empty($storageSetting['cloud_secret_key'])) {
            $sdk = new \QiQiuYun\SDK\QiQiuYunSDK(
                [
                    'access_key' => $storageSetting['cloud_access_key'],
                    'secret_key' => $storageSetting['cloud_secret_key'],
                    'service' => $serviceConfig,
                ],
                $logger
            );
        }

        return $sdk;
    }

    public function generateEsCloudSdk($biz, $serviceConfig, $logger = null)
    {
        $setting = $biz->service('System:SettingService');

        $storageSetting = $setting->get('storage', []);

        $sdk = null;
        if (!empty($storageSetting['cloud_access_key']) && !empty($storageSetting['cloud_secret_key'])) {
            $sdk = new \ESCloud\SDK\ESCloudSDK(
                [
                    'access_key' => $storageSetting['cloud_access_key'],
                    'secret_key' => $storageSetting['cloud_secret_key'],
                    'service' => $serviceConfig,
                ],
                $logger
            );
        }

        return $sdk;
    }

    public function getS2B2CConfig($biz)
    {
        $setting = $biz->service('System:SettingService');
        $developerSetting = $setting->get('developer', []);

        if (!empty($developerSetting['s2b2c_server'])) {
            $urlSegs = explode('://', $developerSetting['s2b2c_server']);
            if (2 == count($urlSegs)) {
                $hostUrl = $urlSegs[1];
            }
        }

        if (empty($hostUrl)) {
            $hostUrl = '';
        }

        return ['s2b2c' => ['host' => $hostUrl]];
    }

    public function getDrpConfig($biz)
    {
        $setting = $biz->service('System:SettingService');
        $developerSetting = $setting->get('developer', []);

        if (!empty($developerSetting['distributor_server'])) {
            $urlSegs = explode('://', $developerSetting['distributor_server']);
            if (2 == count($urlSegs)) {
                $hostUrl = $urlSegs[1];
            }
        }

        if (empty($hostUrl)) {
            $hostUrl = '';
        }

        return ['drp' => ['host' => $hostUrl]];
    }

    public function getESopConfig($biz)
    {
        $setting = $biz->service('System:SettingService');
        $developerSetting = $setting->get('developer', []);

        if (!empty($developerSetting['cloud_api_es_op_server'])) {
            $urlSegs = explode('://', $developerSetting['cloud_api_es_op_server']);
            if (2 == count($urlSegs)) {
                $hostUrl = $urlSegs[1];
            }
        }

        if (empty($hostUrl)) {
            $hostUrl = '';
        }

        return ['esop' => ['host' => $hostUrl]];
    }

    public function getXAPIConfig(Biz $biz)
    {
        $settingService = $biz->service('System:SettingService');
        $siteSettings = $settingService->get('site', []);
        $xapiSetting = $settingService->get('xapi', []);
        $pushUrl = !empty($xapiSetting['push_url']) ? $xapiSetting['push_url'] : 'lrs.qiqiuyun.net/v1/xapi/';
        $pushUrl = ltrim($pushUrl, ' ');
        $pushUrl = rtrim($pushUrl, '/');
        $pushUrl = ltrim($pushUrl, 'http://');
        $pushUrl = ltrim($pushUrl, 'https://');
        $siteName = empty($siteSettings['name']) ? 'none' : $siteSettings['name'];
        $siteUrl = empty($siteSettings['url']) ? '' : $siteSettings['url'];

        return [
            'xapi' => [
                'host' => $pushUrl,
                'school_name' => $siteName,
                'school_url' => $siteUrl,
                'school_version' => System::VERSION,
            ],
        ];
    }

    public function getMpConfig(Biz $biz)
    {
        $setting = $biz->service('System:SettingService');
        $developerSetting = $setting->get('developer', []);
        if (isset($developerSetting['mp_service_url']) && !empty($developerSetting['mp_service_url'])) {
            $urlSegs = explode('://', $developerSetting['mp_service_url']);
            if (2 == count($urlSegs)) {
                $hostUrl = $urlSegs[1];
            }
        }
        if (empty($hostUrl)) {
            $hostUrl = '';
        }

        return ['mp' => ['host' => $hostUrl]];
    }

    public function getAIFaceConfig(Biz $biz)
    {
        $setting = $biz->service('System:SettingService');
        $developerSetting = $setting->get('developer', []);

        if (isset($developerSetting['ai_face_url']) && !empty($developerSetting['ai_face_url'])) {
            $urlSegs = explode('://', $developerSetting['ai_face_url']);
            if (2 == count($urlSegs)) {
                $hostUrl = $urlSegs[1];
            }
        }
        if (empty($hostUrl)) {
            $hostUrl = '';
        }

        return ['ai' => ['host' => $hostUrl]];
    }

    public function getPushConfig(Biz $biz)
    {
        $setting = $biz->service('System:SettingService');
        $developerSetting = $setting->get('developer', []);

        if (isset($developerSetting['push_url']) && !empty($developerSetting['push_url'])) {
            $urlSegs = explode('://', $developerSetting['push_url']);
            if (2 == count($urlSegs)) {
                $hostUrl = $urlSegs[1];
            }
        }
        if (empty($hostUrl)) {
            $hostUrl = '';
        }

        return ['push' => ['host' => $hostUrl]];
    }

    public function getPlayV2Config($biz)
    {
        $setting = $biz->service('System:SettingService');
        $developerSetting = $setting->get('developer', []);

        if (empty($developerSetting['cloud_play_server'])) {
            return [];
        }

        $url = parse_url($developerSetting['cloud_play_server']);

        if (empty($url['host'])) {
            return [];
        }

        return ['playv2' => ['host' => $url['host']]];
    }

    public function getNotificationConfig($biz)
    {
        $setting = $biz->service('System:SettingService');
        $developerSetting = $setting->get('developer', []);

        if (empty($developerSetting['cloud_api_notification_server'])) {
            return [];
        }

        $url = parse_url($developerSetting['cloud_api_notification_server']);

        if (empty($url['host'])) {
            return [];
        }

        return ['notification' => ['host' => $url['host']]];
    }

    public function getWechatConfig($biz)
    {
        $setting = $biz->service('System:SettingService');
        $developerSetting = $setting->get('developer', []);

        if (empty($developerSetting['cloud_api_wechat_server'])) {
            return [];
        }

        $url = parse_url($developerSetting['cloud_api_wechat_server']);

        if (empty($url['host'])) {
            return [];
        }

        return ['wechat' => ['host' => $url['host']]];
    }

    public function getPlatformNewsConfig($biz)
    {
        $setting = $biz->service('System:SettingService');
        $developerSetting = $setting->get('developer', []);

        if (empty($developerSetting['platform_news_api_server'])) {
            return [];
        }

        $url = parse_url($developerSetting['platform_news_api_server']);

        if (empty($url['host'])) {
            return [];
        }

        return ['platformnews' => ['host' => $url['host']]];
    }
}
