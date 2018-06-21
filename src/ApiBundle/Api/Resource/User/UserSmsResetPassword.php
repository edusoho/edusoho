<?php

namespace ApiBundle\Api\Resource\User;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Exception\ErrorCode;
use ApiBundle\Api\Resource\AbstractResource;
use ApiBundle\Api\Annotation\ApiConf;
use Biz\Common\BizSms;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use AppBundle\Common\ArrayToolkit;
use Biz\Common\CommonException;

class UserSmsResetPassword extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function add(ApiRequest $request, $mobile)
    {
        $fields = $request->request->all();
        $smsToken = $this->getBizSms()->send(BizSms::SMS_FORGET_PASSWORD, $mobile);

        return array(
            'smsToken' => $smsToken['token'],
        );
    }

    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function get(ApiRequest $request, $mobile, $code)
    {
        $fields = $request->query->all();
        if (!ArrayToolkit::requireds($fields, array(
            'smsToken',
        ))) {
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

    private function getUserService()
    {
        return $this->biz->service('User:UserService');
    }
}
