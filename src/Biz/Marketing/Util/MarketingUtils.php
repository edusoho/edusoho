<?php

namespace Biz\Marketing\Util;

use Topxia\Service\Common\ServiceKernel;

class MarketingUtils
{
    private static $defaultDomain = 'https://wyx.edusoho.cn';

    public static function getSiteInfo($settingService, $webExtension)
    {
        $site = $settingService->get('site', array());

        $pattern = '/^files\//s';  //内容包含 files/
        $site['logo'] = preg_replace($pattern, '', $site['logo'], 1);  // system/2018/01-30/0951528997cd687447.png
        $consult = $settingService->get('consult', array());
        $wechatFile = isset($consult['webchatURI']) ? $consult['webchatURI'] : '';
        $consult['webchatURI'] = preg_replace($pattern, '', $wechatFile, 1);

        $siteInfo = array(
            'name' => $site['name'],
            'logo' => empty($site['logo']) ? '' : $webExtension->getFurl($site['logo']),
            'about' => $site['slogan'],
            'wechat' => empty($consult['webchatURI']) ? '' : $webExtension->getFurl($consult['webchatURI']),
            'qq' => empty($consult['qq']) ? '' : $consult['qq'][0]['number'],
            'telephone' => empty($consult['phone']) ? '' : $consult['phone'][0]['number'],
        );

        return $siteInfo;
    }

    public static function getSiteInfoWithDomain($settingService, $webExtension, $request)
    {
        $siteInfo = self::getSiteInfo($settingService, $webExtension);
        $siteDomain = $request->getSchemeAndHttpHost();
        $siteInfo['domain'] = $siteDomain;

        return $siteInfo;
    }

    /**
     * @param config array(
     *   'settingService' => $this->getSettingService(),
     *   'webExtension' => $this->getWebExtension(),
     *   'request' => $request,
     *   'currentUser' => $this->getCurrentUser(),
     *   'drpService' => $this->getDistributorUserService()
     * )
     */
    public static function generateLoginFormForCurrentUser($config)
    {
        $site = self::getSiteInfoWithDomain($config['settingService'], $config['webExtension'], $config['request']);
        $user = array(
            'user_source_id' => $config['currentUser']['id'],
            'nickname' => $config['currentUser']['nickname'],
            'avatar' => $config['webExtension']->getFurl($config['currentUser']['largeAvatar'], 'avatar.png'),
        );

        return $config['drpService']->generateLoginForm($user, $site);
    }

    public static function getMarketingDomain()
    {
        $settingService = ServiceKernel::instance()->getBiz()->service('System:SettingService');
        $developerSetting = $settingService->get('developer', array());

        return !empty($developerSetting['marketing_domain']) ? $developerSetting['marketing_domain'] : self::$defaultDomain;
    }
}
