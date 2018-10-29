<?php

namespace ApiBundle\Api\Resource\Me;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\EncryptionToolkit;
use Biz\Common\CommonException;
use AppBundle\Common\ArrayToolkit;
use Biz\Common\BizSms;
use Biz\Sms\SmsException;
use Biz\User\UserException;

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
            throw UserException::ERROR_MOBILE_REGISTERED();
        }

        $password = EncryptionToolkit::XXTEADecrypt(base64_decode($fields['encrypt_password']), $request->getHttpRequest()->getHost());
        if (!$this->getUserService()->verifyPassword($user['id'], $password)) {
            throw UserException::PASSWORD_ERROR();
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

    protected function getUserService()
    {
        return $this->service('User:UserService');
    }
}
