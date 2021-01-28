<?php

namespace ApiBundle\Api\Resource\SmsCenter;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use ApiBundle\Api\Annotation\ApiConf;
use Biz\Common\BizSms;
use Biz\Common\CommonException;
use Biz\System\SettingException;

class SmsCenter extends AbstractResource
{
    private $smsType = array(
        'register' => BizSms::SMS_BIND_TYPE,
    );

    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function add(ApiRequest $request)
    {
        $type = $request->request->get('type');
        if ('register' == $type) {
            $auth = $this->getSettingService()->get('auth', array());
            if (!(isset($auth['register_mode']) && in_array($auth['register_mode'], array('mobile', 'email_or_mobile')))) {
                throw SettingException::FORBIDDEN_MOBILE_REGISTER();
            }
        }

        if (!($type = $request->request->get('type'))
            || !($mobile = $request->request->get('mobile'))) {
            throw CommonException::ERROR_PARAMETER_MISSING();
        }

        $type = $this->convertType($type);

        $smsToken = $this->getBizSms()->send($type, $mobile);
        $this->getUserService()->updateSmsRegisterCaptchaStatus($request->getHttpRequest()->getClientIp());

        return array(
            'smsToken' => $smsToken['token'],
        );
    }

    private function convertType($type)
    {
        if (!array_key_exists($type, $this->smsType)) {
            throw CommonException::ERROR_PARAMETER();
        }

        return $this->smsType[$type];
    }

    /**
     * @return BizSms
     */
    private function getBizSms()
    {
        return $this->biz['biz_sms'];
    }

    private function getUserService()
    {
        return $this->biz->service('User:UserService');
    }

    private function getSettingService()
    {
        return $this->biz->service('System:SettingService');
    }
}
