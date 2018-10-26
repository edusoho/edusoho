<?php

namespace ApiBundle\Api\Resource\Me;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\EncryptionToolkit;
use Biz\Common\CommonException;
use AppBundle\Common\ArrayToolkit;
use Biz\Common\BizSms;
use Biz\Sms\SmsException;

class MeMobile extends AbstractResource
{
    public function update(ApiRequest $request, $mobile)
    {
        $fields = $request->request->all();
        if (!ArrayToolkit::requireds($fields, array(
            'smsToken',
            'smsCode',
            'encrypt_password',
        ))) {
            throw CommonException::ERROR_PARAMETER_MISSING();
        }

        $user = $this->getCurrentUser();
        if (!empty($user['verifyMobile'])) {
            //已经绑定手机号异常
        }

        $password = EncryptionToolkit::XXTEADecrypt(base64_decode($fields['encrypt_password']), $request->getHttpRequest()->getHost());
        if (!$this->getAuthService()->checkPassword($user['id'], $password)) {
            //密码错误异常
        }

        $result = $this->getBizSms()->check(BizSms::SMS_FORGET_PASSWORD, $mobile, $fields['smsToken'], $fields['smsCode']);
        if (BizSms::STATUS_SUCCESS != $result) {
            throw SmsException::FORBIDDEN_SMS_CODE_INVALID();
        }

        $this->getUserService()->changeMobile($user['id'], $verifiedMobile);

        return $this->getUserService()->get($user['id']);
    }

    private function getBizSms()
    {
        return $this->biz['biz_sms'];
    }

    protected function getAuthService()
    {
        return $this->service('User:AuthService');
    }
}
