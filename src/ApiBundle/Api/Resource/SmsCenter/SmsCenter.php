<?php

namespace ApiBundle\Api\Resource\SmsCenter;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Exception\ErrorCode;
use ApiBundle\Api\Resource\AbstractResource;
use ApiBundle\Api\Annotation\ApiConf;
use Biz\Common\BizSms;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class SmsCenter extends AbstractResource
{
    private $smsType = array(
        'register' => BizSms::SMS_BIND_TYPE,
    );

    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function add(ApiRequest $request)
    {
        if (!($type = $request->request->get('type'))
            || !($mobile = $request->request->get('mobile'))) {
            throw new BadRequestHttpException('Params missing', null, ErrorCode::INVALID_ARGUMENT);
        }

        $type = $this->convertType($type);

        $smsToken = $this->getBizSms()->send($type, $mobile);
        $this->getUserService()->updateSmsRegisterCaptchaStatus($request->getHttpRequest()->getClientIp());

        return array(
            'smsToken' => $smsToken['token'],
        );
    }

    private function convertType($type)
    {
        if (!array_key_exists($type, $this->smsType)) {
            throw new BadRequestHttpException('Params error', null, ErrorCode::INVALID_ARGUMENT);
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
}
