<?php 

namespace Topxia\Api\Resource;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;

class UsersBindMobile
{
    public function post(Application $app, Request $request)
    {
        $data        = $request->request->all();
        $mobile      = empty($data['mobile']) ? null : $data['mobile'];
        $captchaCode = empty($data['captcha_code']) ? null : $data['captcha_code'];
        $token       = empty($data['token']) ? null : $data['token'];

        if (empty($mobile)) {
            return $this->error('500', '手机号为空');
        }
        if (empty($captchaCode)) {
            return $this->error('500', '短信验证码为空，请输入');
        }
        if (empty($token)) {
            return $this->error('500', 'token为空');
        }

        if ($this->getUserService()->getUserByVerifiedMobile($mobile)) {
            return $this->error('500', '手机号已被绑定');
        }

        $currentToken = $this->isSmsCaptchaCodeExpire('bind_mobile', $token);
        if (empty($currentToken)) {
            return $this->error('500', '手机验证码已过期');
        }

        //调用SmsService方法
        if ($mobile != $currentToken['data']['mobile']) {
            return $this->error('500', '手机号与短信验证码不匹配');
        }
        if (!empty($currentToken)) {
            if ($captchaCode != $currentToken['data']['captcha_code']) {
                return $this->error('500', '短信验证码错误');
            }
        }

        $user = $this->getCurrentUser();
        $this->getUserService()->changeMobile($user['id'], $mobile);

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
}