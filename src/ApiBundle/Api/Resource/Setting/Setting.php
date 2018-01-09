<?php

namespace ApiBundle\Api\Resource\Setting;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Exception\ErrorCode;
use ApiBundle\Api\Resource\AbstractResource;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class Setting extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function get(ApiRequest $request, $type)
    {
        if (!in_array($type, array('register', 'payment'))) {
            throw new BadRequestHttpException('Type is error', null, ErrorCode::INVALID_ARGUMENT);
        }

        $method = "get${type}";

        return $this->$method();
    }

    public function getRegister()
    {
        $registerSetting = $this->getSettingService()->get('auth', array('register_mode' => 'closed', 'email_enabled' => 'closed'));
        $registerMode = $registerSetting['register_mode'];
        $isEmailVerifyEnable = isset($registerSetting['email_enabled']) && 'opened' == $registerSetting['email_enabled'];
        $registerSetting = $this->getSettingService()->get('auth');
        $level = empty($registerSetting['register_protective']) ? 'none' : $registerSetting['register_protective'];
        $captchaEnabled = 'none' === $level ? false : true;

        return array(
            'mode' => $registerMode,
            'level' => $level,
            'captchaEnabled' => $captchaEnabled,
            'emailVerifyEnabled' => $isEmailVerifyEnable,
        );
    }

    /**
     * @return array
     * @ApiConf(isRequiredAuth=false)
     */
    public function getPayment()
    {
        $paymentSetting = $this->getSettingService()->get('payment', array());

        return array(
            'enabled' => empty($paymentSetting['enabled']) ? false : true,
            'alipayEnabled' => empty($paymentSetting['alipay_enabled']) ? false : true,
            'wxpayEnabled' => empty($paymentSetting['wxpay_enabled']) ? false : true,
            'llpayEnabled' => empty($paymentSetting['llpay_enabled']) ? false : true,
        );
    }

    /**
     * @return \Biz\System\Service\SettingService
     */
    private function getSettingService()
    {
        return $this->service('System:SettingService');
    }
}
