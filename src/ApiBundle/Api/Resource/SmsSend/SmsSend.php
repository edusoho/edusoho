<?php

namespace ApiBundle\Api\Resource\SmsSend;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Common\BizSms;
use Biz\Common\CommonException;
use Biz\System\Service\SettingService;
use Biz\System\SettingException;
use Biz\User\Service\UserService;

class SmsSend extends AbstractResource
{
    private $supportSmsTypes = array(
        'sms_login' => BizSms::SMS_LOGIN,
    );

    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function add(ApiRequest $request)
    {
        $smsType = $request->request->get('type', '');
        $mobile = $request->request->get('mobile', '');

        if (empty($smsType) || empty($mobile)) {
            throw CommonException::ERROR_PARAMETER();
        }

        // 根据业务自主检查设置项
        $this->checkSettingsEnable($smsType);
        // 根据业务自主调用频控器
        $this->checkRateLimit($request, $smsType);

        $bizSmsCode = $this->convertType($smsType);

        // 根据业务自主更新频控器状态
        $smsToken = $this->getBizSms()->send($bizSmsCode, $mobile);

        $this->updateSmsStatus($request->getHttpRequest()->getClientIp(), $smsType);

        return array(
            'smsToken' => $smsToken['token'],
        );
    }

    private function checkSettingsEnable($smsType)
    {
        if ('sms_login' == $smsType) {
            $cloudSms = $this->getSettingService()->get('cloud_sms');
            if (!$cloudSms['sms_enabled']) {
                throw SettingException::FORBIDDEN_SMS_SEND();
            }
        }
    }

    private function checkRateLimit($request, $smsType)
    {
        if ('sms_login' == $smsType) {
            $this->handleRateLimiter($request, 'sms_login_rate_limiter');
        }
    }

    private function updateSmsStatus($clientIp, $smsType)
    {
        if ('sms_login' == $smsType) {
            $this->getUserService()->getSmsCommonCaptchaStatus($clientIp, true);
        }
    }

    private function convertType($smsType)
    {
        if (!array_key_exists($smsType, $this->supportSmsTypes)) {
            throw CommonException::ERROR_PARAMETER();
        }

        return $this->supportSmsTypes[$smsType];
    }

    private function handleRateLimiter(ApiRequest $request, $limiterName)
    {
        $biz = $this->biz;
        $limiter = $biz[$limiterName];
        $limiter->handle($request->getHttpRequest());
    }

    /**
     * @return BizSms
     */
    private function getBizSms()
    {
        return $this->biz['biz_sms'];
    }

    /**
     * @return UserService
     */
    private function getUserService()
    {
        return $this->biz->service('User:UserService');
    }

    /**
     * @return SettingService
     */
    private function getSettingService()
    {
        return $this->biz->service('System:SettingService');
    }
}
