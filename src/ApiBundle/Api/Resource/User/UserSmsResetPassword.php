<?php

namespace ApiBundle\Api\Resource\User;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Exception\ErrorCode;
use ApiBundle\Api\Resource\AbstractResource;
use ApiBundle\Api\Annotation\ApiConf;
use Biz\Common\BizSms;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class UserSmsResetPassword extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function add(ApiRequest $request, $mobile)
    {
        // $fields = $request->request->all();
        // $smsToken = $this->getBizSms()->send(BizSms::SMS_FORGET_PASSWORD);

        // return array(
        //     'smsToken' => $smsToken['token'],
        // );
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
}
