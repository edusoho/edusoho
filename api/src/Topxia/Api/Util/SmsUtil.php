<?php 

namespace Topxia\Api\Util;

use Topxia\Service\Common\ServiceKernel;

class SmsUtil
{
    public function verifySmsCode($type, $smsCode, $smsToken)
    {
        $token = $this->getTokenService()->verifyToken($type, $smsToken);

        if (empty($token)) {
            return 'sms_code_expired';
        }
        if ($smsCode != $token['data']['sms_code']) {
            return 'sms_code_error';
        }

        return true;
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