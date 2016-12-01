<?php 

namespace Topxia\Api\Resource;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;

class VerifiedMobile extends BaseResource
{
    public function post(Application $app, Request $request)
    {
        $mobile   = $request->request->get('mobile');
        $smsCode  = $request->request->get('smsCode');
        $smsToken = $request->request->get('smsToken');

        if (empty($mobile)) {
            return $this->error('500', '手机号为空');
        }
        if (empty($smsCode)) {
            return $this->error('500', '短信验证码为空，请输入');
        }
        if (empty($smsToken)) {
            return $this->error('500', 'token为空');
        }

        $token = $this->getTokenService()->verifyToken($smsToken);
        if (empty($token)) {
            return $this->error('500', '手机验证码已过期');
        }
        if ($mobile != $token['data']['mobile']) {
            return $this->error('500', '手机号与短信验证码不匹配');
        }
        if ($this->getUserService()->getUserByVerifiedMobile($mobile)) {
            return $this->error('500', '手机号已被绑定');
        }

        if ($smsCode != $token['data']['sms_code']) {
            return $this->error('500', '短信验证码错误');
        }

        $user = $this->getCurrentUser();
        $this->getUserService()->changeMobile($user['id'], $mobile);
        $this->getTokenService()->destoryToken($token);

        return array('userId' => $user['id'], 'mobile' => $mobile);
    }

    public function filter($res)
    {
        return $res;
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