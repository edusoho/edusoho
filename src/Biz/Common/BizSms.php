<?php

namespace Biz\Common;

use AppBundle\Common\Exception\UnexpectedValueException;
use AppBundle\Common\TimeMachine;
use Codeages\Biz\Framework\Context\BizAware;

class BizSms extends BizAware
{
    const STATUS_SUCCESS = 'sms.code.success';

    const STATUS_INVALID = 'sms.code.invalid';

    const STATUS_EXPIRED = 'sms.code.expired';

    const SMS_BIND_TYPE = 'sms_bind';

    const SMS_REGISTER = 'sms_registration';

    const SMS_FORGET_PASSWORD = 'sms_forget_password';

    const SMS_LOGIN = 'sms_login';

    public function send($smsType, $mobile, $options = array())
    {
        $options = array_merge(array('duration' => TimeMachine::HALF_HOUR, 'times' => 10, 'userId' => 0), $options);
        $result = $this->getSmsService()->sendVerifySms($smsType, $mobile, 0);

        if (isset($result['error'])) {
            throw new UnexpectedValueException($result['error'], 500);
        }

        $smsToken = $this->getTokenService()->makeToken($smsType, array(
            'times' => $options['times'],
            'duration' => $options['duration'],
            'userId' => $options['userId'],
            'data' => array(
                'code' => $result['captcha_code'],
                'mobile' => $mobile,
            ),
        ));

        return $smsToken;
    }

    public function check($smsType, $mobile, $smsToken, $code)
    {
        $token = $this->getTokenService()->verifyToken($smsType, $smsToken);
        if (empty($token)) {
            return self::STATUS_INVALID;
        }

        $remainedTimes = $token['remainedTimes'];

        if (0 == $remainedTimes) {
            return self::STATUS_EXPIRED;
        }

        if ($token['data']['code'] !== $code || $token['data']['mobile'] !== $mobile) {
            return self::STATUS_INVALID;
        }

        return self::STATUS_SUCCESS;
    }

    /**
     * @return \Biz\Sms\Service\SmsService
     */
    private function getSmsService()
    {
        return $this->biz->service('Sms:SmsService');
    }

    /**
     * @return \Biz\User\Service\TokenService
     */
    private function getTokenService()
    {
        return $this->biz->service('User:TokenService');
    }
}
