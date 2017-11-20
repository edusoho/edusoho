<?php

namespace Biz\Common;

use AppBundle\Common\TimeMachine;
use Codeages\Biz\Framework\Context\BizAware;

class BizSms extends BizAware
{
    const STATUS_SUCCESS = 'success';

    const STATUS_INVALID = 'invalid';

    const STATUS_EXPIRED = 'expired';

    const SMS_BIND_TYPE = 'sms_bind';

    public function send($smsType, $mobile, $options = array())
    {
        $options = array_merge(array('duration' => TimeMachine::HALF_HOUR, 'verify_times' => 10, 'userId' => 0), $options);
        $result = $this->getSmsService()->sendVerifySms($smsType, $mobile, 0);

        $smsToken = $this->getTokenService()->makeToken($smsType, array(
            'times' => $options['verify_times'],
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

        if ($token['data']['code'] !== $code || $token['data']['code'] !== $mobile) {
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
