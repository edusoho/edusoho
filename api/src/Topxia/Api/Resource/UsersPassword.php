<?php 

namespace Topxia\Api\Resource;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;

class UsersPassword extends BaseResource
{
    public function post(Application $app, Request $request)
    {
        $data        = $request->request->all();
        $mobile      = empty($data['mobile']) ? null : $data['mobile'];
        $password    = empty($data['password']) ? null : $data['password'];
        $captchaCode = empty($data['captcha_code']) ? null : $data['captcha_code'];
        $token       = empty($data['token']) ? null : $data['token'];

        if (empty($password)) {
            return $this->error('500', '未输入新密码');
        }
        if (empty($captchaCode)) {
            return $this->error('500', '短信验证码为空，请输入');
        }
        if (empty($token)) {
            return $this->error('500', 'token字段为空');
        }
        if (empty($mobile)) {
            return $this->error('500', '手机号为空');
        }

        $currentToken = $this->isSmsCaptchaCodeExpire('change_password', $token);
        if (empty($currentToken)) {
            return $this->error('500', '手机验证码已过期');
        }

        if ($mobile != $currentToken['data']['mobile']) {
            return $this->error('500', '手机号与短信验证码不匹配');
        }
        if (!empty($currentToken)) {
            if ($captchaCode != $currentToken['data']['captcha_code']) {
                return $this->error('500', '短信验证码错误');
            }
        }

        $user = $this->getCurrentUser();
        if ($user['id'] == 0) {
            $targetUser = $this->getUserService()->getUserByVerifiedMobile($mobile);

            if (empty($targetUser)) {
                return $this->error('500', '该手机号未被绑定');
            }

            $user['id'] = $targetUser['id'];
        }

        $this->getUserService()->changePassword($user['id'], $password);
        $this->getTokenService()->destoryToken($currentToken);

        return array('code' => 0);
    }

    public function filter($res)
    {
        return $res;
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
}