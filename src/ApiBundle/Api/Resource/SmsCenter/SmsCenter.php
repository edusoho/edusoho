<?php

namespace ApiBundle\Api\Resource\SmsCenter;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\BehaviorVerification\Service\BehaviorVerificationService;
use Biz\Common\BizSms;
use Biz\Common\CommonException;
use Biz\System\SettingException;
use Symfony\Component\HttpFoundation\JsonResponse;

class SmsCenter extends AbstractResource
{
    private $smsType = [
        'register' => BizSms::SMS_BIND_TYPE,
        'smsBind' => BizSms::SMS_BIND_TYPE,
    ];

    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function add(ApiRequest $request)
    {
        if (!($request->getHttpRequest()->isXmlHttpRequest())) {
            $mobileSetting = $this->getSettingService()->get('mobile',array());
            $wap = $this->getSettingService()->get('wap',array());
            if ($mobileSetting['enabled'] == 0 && $wap['template'] != 'sail'){
                return null;
            }
        }

        if ($this->getBehaviorVerificationService()->behaviorVerification($request->getHttpRequest())){
            return new JsonResponse(['ACK' => 'ok', "allowance" => 0]);
        }

        $type = $request->request->get('type');

        if (!$type || !($mobile = $request->request->get('mobile'))) {
            throw CommonException::ERROR_PARAMETER_MISSING();
        }

        $smsType = $this->convertType($type);

        return $this->$type($request, $smsType, $mobile);
    }

    protected function register(ApiRequest $request, $type, $mobile)
    {
        $auth = $this->getSettingService()->get('auth', []);
        if (!(isset($auth['register_mode']) && in_array($auth['register_mode'], ['mobile', 'email_or_mobile']))) {
            throw SettingException::FORBIDDEN_MOBILE_REGISTER();
        }

        $unique = $request->request->get('unique', 1);
        $smsToken = $this->getBizSms()->send($type, $mobile, [], $unique);
        $this->getUserService()->updateSmsRegisterCaptchaStatus($request->getHttpRequest()->getClientIp());

        return [
            'smsToken' => $smsToken['token'],
        ];
    }

    protected function smsBind(ApiRequest $request, $type, $mobile)
    {
        $unique = $request->request->get('unique', 1);
        $result = $this->getBizSms()->send($type, $mobile, [], $unique);

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

    /**
     * @return BehaviorVerificationService
     */
    protected function getBehaviorVerificationService()
    {
        return $this->biz->service('BehaviorVerification:BehaviorVerificationService');
    }
}
