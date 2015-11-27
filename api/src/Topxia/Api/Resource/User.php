<?php

namespace Topxia\Api\Resource;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class User extends BaseResource
{

    public function filter(&$res)
    {

        unset($res['password']);
        unset($res['salt']);
        unset($res['payPassword']);
        unset($res['payPasswordSalt']);
       
        $res['promotedTime'] = date('c', $res['promotedTime']);
        $res['lastPasswordFailTime'] = date('c', $res['lastPasswordFailTime']);
        $res['loginTime'] = date('c', $res['loginTime']);
        $res['approvalTime'] = date('c', $res['approvalTime']);
        $res['createdTime'] = date('c', $res['createdTime']);
        $res['userId'] = $res['id'];
        $res['mobile'] = $res['verifiedMobile'];
        unset($res['id']);
        unset($res['verifiedMobile']);
        unset($res['uri']);
        unset($res['type']);
        unset($res['point']);
        unset($res['coin']);
        unset($res['emailVerified']);
        unset($res['setup']);
        unset($res['promoted']);
        unset($res['promotedTime']);
        unset($res['locked']);//TODO 是否需要处理
        unset($res['lockDeadline']);//TODO 是否需要处理
        unset($res['lastPasswordFailTime']);
        unset($res['consecutivePasswordErrorTimes']);
        unset($res['loginTime']);
        unset($res['loginIp']);
        unset($res['loginSessionId']);
        unset($res['approvalTime']);
        unset($res['approvalStatus']);
        unset($res['newMessageNum']);
        unset($res['newNotificationNum']);
        unset($res['smallAvatar']);
        unset($res['mediumAvatar']);
        unset($res['largeAvatar']);

        unset($res['createdIp']);


        return $res;
    }

    public function simplify($res)
    {
        $simple = array();

        $simple['id'] = $res['id'];
        $simple['nickname'] = $res['nickname'];
        $simple['title'] = $res['title'];
        $simple['avatar'] = $this->getFileUrl($res['smallAvatar']);

        return $simple;
    }

}