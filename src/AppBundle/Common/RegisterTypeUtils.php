<?php

namespace AppBundle\Common;

class RegisterTypeUtils
{
    public static function getRegisterTypes($registrations)
    {
        $regTypes = array();
        if (!empty($registrations['verifiedMobile'])) {
            $regTypes[] = 'mobile';
        }

        if (!empty($registrations['email'])) {
            $regTypes[] = 'email';
        }

        if (!empty($registrations['type']) &&
                in_array($registrations['type'], array('qq', 'weibo', 'renren', 'weixinweb', 'weixinmob'))) {
            $regTypes[] = 'binder';
        }

        if (!empty($registrations['distributorToken'])) {
            $regTypes[] = 'distributor';
        }

        return $regTypes;
    }
}
