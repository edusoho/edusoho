<?php

namespace AppBundle\Common;

class RegisterTypeUtils
{
    public static function getRegisterTypes($registrations, $type = '')
    {
        $regTypes = array();
        if (!empty($registrations['verifiedMobile'])) {
            $regTypes[] = 'mobile';
        }

        if (!empty($registrations['email'])) {
            $regTypes[] = 'email';
        }

        if (!empty($type) && in_array($type, array('qq', 'weibo', 'renren', 'weixinweb', 'weixinmob'))) {
            $regTypes[] = 'binder';       
        }

        return $regTypes;
    }

    
}
