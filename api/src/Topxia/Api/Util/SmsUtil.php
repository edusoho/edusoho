<?php 

namespace Topxia\Api\Util;

use Topxia\Service\Common\ServiceKernel;

class SmsUtil
{
    public function verifySmsCode($type, $smsCode, $smsToken)
    {
        $token = $this->getTokenService()->verifyToken($type, $smsToken);

        if (empty($token)) {
            throw new \Exception('验证码已过期');
        }
        if ($smsCode != $token['data']['sms_code']) {
            throw new \Exception("验证码错误");
        }

        return true;
    }

    protected function getTokenService()
    {
        return ServiceKernel::instance()->createService('User.TokenService');
    }

    protected function getUserService()
    {
        return ServiceKernel::instance()->createService('User.UserService');
    }
}