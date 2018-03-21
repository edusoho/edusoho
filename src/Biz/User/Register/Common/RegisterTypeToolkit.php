<?php

namespace Biz\User\Register\Common;

class RegisterTypeToolkit
{
    public function getRegisterTypes($registrations)
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

    /**
     * 第三方登录注册时，生成的registerTypes规则
     *
     * @param $accountType 分为 email 或 mobile
     * @param $registrations
     * array(
     *  'distributorToken' => '....', // 如果从分销平台注册过来，需要带上 distributorToken
     * )
     */
    public function getThirdPartyRegisterTypes($accountType, $registrations)
    {
        $regTypes = array($accountType, 'binder');

        if (!empty($registrations['distributorToken'])) {
            $regTypes[] = 'distributor';
        }

        return $regTypes;
    }
}
