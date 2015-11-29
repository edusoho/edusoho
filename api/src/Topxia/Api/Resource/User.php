<?php

namespace Topxia\Api\Resource;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class User extends BaseResource
{
    private $_unsetFields = array(
        'password', 'salt', 'payPassword', 'payPasswordSalt',
    );

    private $_publicFields = array(
        'id', 'nickname' , 'title', 'isTeacher', 'point', 'smallAvatar', 'mediumAvatar', 'largeAvatar', 'about',
    );

    private $_privateFields = array(
        'id',  'nickname' , 'title', 'tags', 'type', 'isTeacher', 
        'point', 'coin', 'smallAvatar', 'mediumAvatar', 'largeAvatar', 'about',
        'email', 'emailVerified', 'roles', 'promoted', 'promotedTime', 
        'locked', 'lockDeadline',
        'loginTime', 'loginIp', 'approvalTime', 'approvalStatus', 'newMessageNum', 'newNotificationNum',
        'createdIp', 'createdTime'
    );

    private $_profileFields = array(
        'truename', 'idcard', 'gender', 'birthday', 'city', 'mobile', 'qq', 
        'signature', 'about', 'company', 'job', 'school', 'class', 'weibo', 'weixin', 'site',
    );

    public function filter(&$res)
    {
        foreach ($this->_unsetFields as $key) {
            unset($res[$key]);
        }

        $res['isTeacher'] = in_array('ROLE_TEACHER', $res['roles']);
        $res['about'] = $res['profile']['about'];

        $currentUser = getCurrentUser();

        $returnRes = array();
        // if ($currentUser->isAdmin() || ($currentUser['id'] == $res['id'])) {

            foreach ($this->_privateFields as $key) {
                $returnRes[$key] = $res[$key];
            }

            foreach ($this->_profileFields as $key) {
                $returnRes[$key] = $res['profile'][$key];
            }

        // } else {
        //     foreach ($this->_publicFields as $key) {
        //         $returnRes[$key] = $res[$key];
        //     }
        // }

        $res = $returnRes;

        return $returnRes;
       
        $res['promotedTime'] = date('c', $res['promotedTime']);
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