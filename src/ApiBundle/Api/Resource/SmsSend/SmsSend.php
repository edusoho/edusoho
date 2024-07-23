<?php

namespace ApiBundle\Api\Resource\SmsSend;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Common\BizSms;
use Biz\Common\CommonException;
use Biz\SmsDefence\Service\SmsDefenceService;
use Biz\System\SettingException;
use Biz\User\Service\UserService;
use Biz\User\UserException;
use Symfony\Component\HttpFoundation\JsonResponse;

class SmsSend extends AbstractResource
{
    private $supportSmsTypes = [
        'sms_login' => BizSms::SMS_LOGIN,
        'sms_bind' => BizSms::SMS_BIND_TYPE,
    ];

    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function add(ApiRequest $request)
    {
        if (!($request->getHttpRequest()->isXmlHttpRequest())) {
            $mobileSetting = $this->getSettingService()->get('mobile', []);
            $wap = $this->getSettingService()->get('wap', []);
            if (0 == $mobileSetting['enabled'] && 'sail' != $wap['template']) {
                return null;
            }
        }
        if ($request->getHttpRequest()->isXmlHttpRequest()) {
            $fields = [
                'fingerprint' => $request->getHttpRequest()->get('encryptedPoint'),
                'userAgent' => $request->getHttpRequest()->headers->get('user-agent'),
                'ip' => $request->getHttpRequest()->getClientIp(),
                'mobile' => $request->getHttpRequest()->get('mobile'),
            ];
            if ($this->getSmsDefenceService()->validate($fields)) {
                return new JsonResponse(['ACK' => 'ok', 'allowance' => 0]);
            }
        }
        $smsType = $request->request->get('type', '');
        $mobile = $request->request->get('mobile', '');
        $allowNotExistMobile = $request->request->get('allowNotExistMobile', 1);

        $user = $this->getUserService()->getUserByVerifiedMobile($mobile);

        if (!empty($user) && $user['locked']) {
            throw UserException::LOCKED_USER();
        }

        if (!in_array($smsType, $this->supportSmsTypes)) {
            throw CommonException::ERROR_PARAMETER();
        }

        if (!$allowNotExistMobile && !$this->getUserService()->getUserByVerifiedMobile($mobile)) {
            throw UserException::MOBILE_NOT_FOUND();
        }

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

        return [
            'smsToken' => $smsToken['token'],
        ];
    }

    private function checkSettingsEnable($smsType)
    {
        $cloudSms = $this->getSettingService()->get('cloud_sms');
        if (!$cloudSms['sms_enabled']) {
            throw SettingException::FORBIDDEN_SMS_SEND();
        }
    }

    private function checkRateLimit($request, $smsType)
    {
        $this->handleRateLimiter($request, 'sms_login_rate_limiter');
    }

    private function updateSmsStatus($clientIp, $smsType)
    {
        $this->getUserService()->getSmsCommonCaptchaStatus($clientIp, true);
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
     * @return SmsDefenceService
     */
    protected function getSmsDefenceService()
    {
        return $this->biz->service('SmsDefence:SmsDefenceService');
    }
}
