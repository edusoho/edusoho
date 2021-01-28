<?php

namespace ApiBundle\Api\Resource\Me;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Common\CommonException;
use AppBundle\Common\ArrayToolkit;
use Biz\Common\BizSms;
use Biz\Sms\SmsException;
use Biz\User\UserException;
use ApiBundle\Api\Annotation\ResponseFilter;

class MeMobile extends AbstractResource
{
    /**
     * @ResponseFilter(class="ApiBundle\Api\Resource\Me\MeFilter", mode="simple")
     */
    public function update(ApiRequest $request, $mobile)
    {
        $fields = $request->request->all();
        if (!ArrayToolkit::requireds($fields, array(
            'smsToken',
            'smsCode',
            'password',
        ))) {
            throw CommonException::ERROR_PARAMETER_MISSING();
        }

        $user = $this->getCurrentUser();
        if (!empty($user['verifyMobile'])) {
            throw UserException::ERROR_MOBILE_REGISTERED();
        }

        if (!$this->getUserService()->verifyPassword($user['id'], $fields['password'])) {
            throw UserException::PASSWORD_ERROR();
        }

        $result = $this->getBizSms()->check(BizSms::SMS_BIND_TYPE, $mobile, $fields['smsToken'], $fields['smsCode']);
        if (BizSms::STATUS_SUCCESS != $result) {
            throw SmsException::FORBIDDEN_SMS_CODE_INVALID();
        }

        $this->getUserService()->changeMobile($user['id'], $mobile);

        return $this->getUserService()->getUser($user['id']);
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
