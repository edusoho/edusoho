<?php

namespace Topxia\WebBundle\Util;

use Topxia\Service\Common\ServiceKernel;

class AvatarAlert
{
    public static function alertJoinCourse($user)
    {
        $setting = self::getSettingService()->get('user_partner');

        if (empty($setting['avatar_alert'])) {
            return false;
        }

        if ($setting['avatar_alert'] == 'open' && $user['mediumAvatar'] == '') {
            return true;
        }

        return false;
    }

    public static function alertInMyCenter($user)
    {
        $setting = self::getSettingService()->get('user_partner');

        if (empty($setting['avatar_alert'])) {
            return false;
        }

        if ($user['mediumAvatar'] == '') {
            return true;
        }

        return false;
    }

    protected static function getSettingService()
    {
        return ServiceKernel::instance()->createService('System.SettingService');
    }
}
