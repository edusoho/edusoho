<?php

namespace Topxia\WebBundle\Util;

use Topxia\Service\Common\ServiceKernel;
use Topxia\Service\System\SettingService;

class SetAvatarHelper
{
	 public static function setAvatarWhenJoinCourse($user)
	 {
	 	$isShouldSetAvatar = self::getSettingService()->get('user_partner');
        
        if ($isShouldSetAvatar['isShouldSetAvatar'] =='enforce_avatar' && $user['mediumAvatar'] == '') {
            return 'yes';
        }
       
        return 'no';
	 }

    public static function setAvatarGotoMyCourse($user)
     {
        $isShouldSetAvatar = self::getSettingService()->get('user_partner');
        
        if ($isShouldSetAvatar['isShouldSetAvatar'] =='notify_avatar' && $user['mediumAvatar'] == '') {
            return 'yes';
        }
       
        return 'no';
     }

	 protected static function getSettingService()
    {
        return ServiceKernel::instance()->createService('System.SettingService');
    }

}

