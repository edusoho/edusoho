<?php

namespace Topxia\Api\Resource\User;

use Silex\Application;
use Topxia\Api\Util\SmsUtil;
use Topxia\Api\Resource\BaseResource;
use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\HttpFoundation\Request;

class VerifiedMobile extends BaseResource
{
    public function post(Application $app, Request $request)
    {
        $mobile = $request->request->get('mobile');
        $smsCode = $request->request->get('sms_code');
        $smsToken = $request->request->get('sms_token');

        if (empty($mobile)) {
            return $this->error('500', '手机号为空');
        }

        if (empty($smsCode)) {
            return $this->error('500', '短信验证码为空，请输入');
        }
        if (empty($smsToken)) {
            return $this->error('500', 'token为空');
        }

        $smsUtil = new SmsUtil();
        $result = $smsUtil->verifySmsCode('sms_verify_mobile', $smsCode, $smsToken);

        if (true != $result) {
            if ('sms_code_expired' == $result) {
                return $this->error('sms_code_expired', '验证码已过期');
            } else {
                return $this->error('500', '验证码错误');
            }
        }
        $userInfo = $this->getUserService()->getUserByVerifiedMobile($mobile);
        if ($userInfo) {
            return $this->error('500', '手机号已绑定');
        }

        $user = $this->getCurrentUser();
        $this->getUserService()->changeMobile($user['id'], $mobile);
        $this->getTokenService()->destoryToken($smsToken);

        return array('userId' => $user['id'], 'mobile' => $mobile);
    }

    public function filter($res)
    {
        return $res;
    }

    protected function getTokenService()
    {
        return ServiceKernel::instance()->createService('User:TokenService');
    }

    protected function getUserService()
    {
        return ServiceKernel::instance()->createService('User:UserService');
    }
}
