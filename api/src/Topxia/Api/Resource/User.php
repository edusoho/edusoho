<?php

namespace Topxia\Api\Resource;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class User extends BaseResource
{
    private $_unsetFields = array(
        'password', 'salt', 'payPassword', 'payPasswordSalt'
    );

    private $_publicFields = array(
        'id', 'nickname', 'title', 'point', 'smallAvatar', 'mediumAvatar', 'largeAvatar', 'createdTime', 'updatedTime'
    );

    private $_publicProfileFields = array(
        'about'
    );

    private $_privateFields = array(
        'id', 'nickname', 'title', 'tags', 'type', 'roles',
        'point', 'coin', 'smallAvatar', 'mediumAvatar', 'largeAvatar',
        'email', 'emailVerified', 'promoted', 'promotedTime', 'locked', 'lockDeadline',
        'loginTime', 'loginIp', 'approvalTime', 'approvalStatus', 'newMessageNum', 'newNotificationNum',
        'createdIp', 'createdTime', 'updatedTime'
    );

    private $_privateProfileFields = array(
        'truename', 'idcard', 'gender', 'birthday', 'city', 'mobile', 'qq',
        'signature', 'about', 'company', 'job', 'school', 'class', 'weibo', 'weixin', 'site'
    );

    public function get(Application $app, Request $request, $id)
    {
        $user = $this->getUserService()->getUser($id);

        if (empty($user)) {
            return $this->error(404, "用户(#{$id})不存在");
        }

        $user['profile'] = $this->getUserService()->getUserProfile($id);

        return $this->filter($user);
    }

    public function post(Application $app, Request $request, $id)
    {
        $data    = $request->request->all();

        $type = $data['type'];

        if (empty($type)) {
            return $this->errer('5005', '没有type字段');
        }

        $method = 'call_'.$type;

        if (method_exists($this, $method)) {
            return call_user_func(array($this, $method), $request);
        } else {
            return $this->error('5006', 'type字段无效');
        }
    }

    public function filter($res)
    {
        foreach ($this->_unsetFields as $key) {
            unset($res[$key]);
        }

        $currentUser = getCurrentUser();

        $returnRes = array();

        if ($currentUser->isAdmin() || ($currentUser['id'] == $res['id'])) {
            foreach ($this->_privateFields as $key) {
                $returnRes[$key] = $res[$key];
            }

            if (!empty($res['profile'])) {
                foreach ($this->_privateProfileFields as $key) {
                    $returnRes[$key] = $res['profile'][$key];
                }
            }
        } else {
            foreach ($this->_publicFields as $key) {
                $returnRes[$key] = $res[$key];
            }

            // if (in_array('ROLE_TEACHER', $returnRes['roles'])) {
            //     $returnRes['roles'] = array('ROLE_TEACHER');
            // } else {
            //     $returnRes['roles'] = array('ROLE_USER');
            // }
            if (!empty($res['profile'])) {
                foreach ($this->_publicProfileFields as $key) {
                    $returnRes[$key] = $res['profile'][$key];
                }
            }
        }

        $res = $returnRes;

        foreach (array('smallAvatar', 'mediumAvatar', 'largeAvatar') as $key) {
            $res[$key] = $this->getFileUrl($res[$key]);
        }

        foreach (array('promotedTime', 'loginTime', 'approvalTime', 'createdTime') as $key) {
            if (!isset($res[$key])) {
                continue;
            }

            $res[$key] = date('c', $res[$key]);
        }

        $res['updatedTime'] = date('c', $res['updatedTime']);

        return $res;
    }

    public function simplify($res)
    {
        $simple = array();

        $simple['id']       = $res['id'];
        $simple['nickname'] = $res['nickname'];
        $simple['title']    = $res['title'];
        $simple['avatar']   = $this->getFileUrl($res['smallAvatar']);

        return $simple;
    }

    protected function call_bind_mobile($request)
    {
        // $user     = $this->getCurrentUser();
        $data        = $request->request->all();
        $mobile      = empty($data['mobile']) ? null : $data['mobile'];
        $captchaCode = empty($data['captcha_code']) ? null : $data['captcha_code'];
        $token       = empty($data['token']) ? null : $data['token'];

        if (empty($mobile)) {
            return $this->error('5015', '手机号为空');
        }
        if (empty($captchaCode)) {
            return $this->error('5016', '短信验证码为空，请输入');
        }
        if (empty($token)) {
            return $this->error('5019', 'token为空');
        }

        if ($this->getUserService()->getUserByVerifiedMobile($mobile)) {
            return $this->error('5017', '手机号已被绑定');
        }

        $currentToken = $this->isSmsCaptchaCodeExpire('bind_mobile', $token);
        if (empty($currentToken)) {
            return $this->error('5013', '手机验证码已过期');
        }

        //调用SmsService方法
        if ($mobile != $currentToken['data']['mobile']) {
            return $this->error('5018', '手机号与短信验证码不匹配');
        }
        if (!empty($currentToken)) {
            if ($captchaCode != $currentToken['data']['captcha_code']) {
                return $this->error('5014', '短信验证码错误');
            }
        }

        $user = $this->getCurrentUser();
        $this->getUserService()->changeMobile($user['id'], $mobile);

        return array('code' => 0);
    }

    protected function isSmsCaptchaCodeExpire($type, $token)
    {
        $currentToken = $this->getTokenService()->verifyToken($type, $token);

        if (empty($currentToken)) {
            return array();
        }

        return $currentToken;
    }

    protected function getTokenService()
    {
        return $this->getServiceKernel()->createService('User.TokenService');
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }

    protected function getSmsService()
    {
        return $this->getServiceKernel()->createService('Sms.SmsService');
    }
}
