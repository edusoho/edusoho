<?php

namespace ApiBundle\Api\Resource\User;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use Biz\BehaviorVerification\Service\BehaviorVerificationBlackIpService;
use Biz\BehaviorVerification\Service\BehaviorVerificationCoordinateService;
use Biz\BehaviorVerification\Service\BehaviorVerificationService;
use Biz\Common\BizSms;
use Biz\Common\CommonException;
use Biz\User\Service\UserService;
use Biz\User\UserException;
use function Clue\StreamFilter\register;

class UserSmsResetPassword extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function add(ApiRequest $request, $mobile)
    {
        if (!($request->getHttpRequest()->isXmlHttpRequest())) {
            $mobileSetting = $this->getSettingService()->get('mobile',array());
            $wap = $this->getSettingService()->get('wap',array());
            if ($mobileSetting['enabled'] == 0 && $wap['template'] != 'sail'){
                return null;
            }
        }

        if (!$this->getUserService()->getUserByVerifiedMobile($mobile)) {
            throw UserException::MOBILE_NOT_FOUND();
        }

        $token = $request->request->get('dragCaptchaToken', '');
        $this->getDragCaptcha()->check($token);
        if ($this->getBehaviorVerificationService()->behaviorVerification($request->getHttpRequest())) {
            return [
                'smsToken' => 'fakeToken',
            ];
        }
        $smsToken = $this->getBizSms()->send(BizSms::SMS_FORGET_PASSWORD, $mobile);

        return [
            'smsToken' => $smsToken['token'],
        ];
    }

    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function get(ApiRequest $request, $mobile, $code)
    {
        $fields = $request->query->all();
        if (!ArrayToolkit::requireds($fields, [
            'smsToken',
        ])) {
            throw CommonException::ERROR_PARAMETER_MISSING();
        }

        return $this->getBizSms()->check(BizSms::SMS_FORGET_PASSWORD, $mobile, $fields['smsToken'], $code);
    }

    /**
     * @return BizSms
     */
    private function getBizSms()
    {
        return $this->biz['biz_sms'];
    }

    public function getDragCaptcha()
    {
        return $this->biz['biz_drag_captcha'];
    }

    /**
     * @return UserService
     */
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
