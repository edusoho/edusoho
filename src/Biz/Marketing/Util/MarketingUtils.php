<?php

namespace Biz\Marketing\Util;

class MarketingUtils
{
    public static function getSiteInfo($settingService, $webExtension)
    {
        $site = $settingService->get('site', array());

        $site['logo'] = preg_replace('#files/#', '', $site['logo'], 1);
        $consult = $settingService->get('consult', array());
        $wechatFile = isset($consult['webchatURI']) ? $consult['webchatURI'] : '';
        $consult['webchatURI'] = preg_replace('#files/#', '', $wechatFile, 1);

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
    public function generateLoginFormForCurrentUser($config)
    {
        $site = self::getSiteInfoWithDomain($config['settingService'], $config['webExtension'], $config['request']);
        $user = array(
            'user_source_id' => $config['currentUser']['id'],
            'nickname' => $config['currentUser']['nickname'],
            'avatar' => $config['webExtension']->getFurl($config['currentUser']['largeAvatar'], 'avatar.png'),
        );

        return $config['drpService']->generateLoginForm($user, $site);
    }
}
