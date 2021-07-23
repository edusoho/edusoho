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
        'smsBind' => BizSms::SMS_BIND_TYPE,
    );

    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function add(ApiRequest $request)
    {
        $type = $request->request->get('type');

        if (!$type || !($mobile = $request->request->get('mobile'))) {
            throw CommonException::ERROR_PARAMETER_MISSING();
        }

        $smsType = $this->convertType($type);

        return $this->$type($request, $smsType, $mobile);
    }

    protected function register($request, $type, $mobile)
    {
        $auth = $this->getSettingService()->get('auth', array());
        if (!(isset($auth['register_mode']) && in_array($auth['register_mode'], array('mobile', 'email_or_mobile')))) {
            throw SettingException::FORBIDDEN_MOBILE_REGISTER();
        }

        $smsToken = $this->getBizSms()->send($type, $mobile);
        $this->getUserService()->updateSmsRegisterCaptchaStatus($request->getHttpRequest()->getClientIp());

        return [
            'smsToken' => $smsToken['token'],
        ];
    }

    protected function smsBind($request, $type, $mobile)
    {
        $result = $this->getBizSms()->send($type, $mobile);

        $this->getUserService()->getSmsCommonCaptchaStatus($request->getHttpRequest()->getClientIp(), true);

        return [
            'smsToken' => $result['token'],
        ];
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
