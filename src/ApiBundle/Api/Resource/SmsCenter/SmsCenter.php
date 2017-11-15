<?php

namespace ApiBundle\Api\Resource\SmsCenter;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Exception\ErrorCode;
use ApiBundle\Api\Resource\AbstractResource;
use ApiBundle\Api\Annotation\ApiConf;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class SmsCenter extends AbstractResource
{
    private $smsType = array(
        'register' => 'sms_bind'
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

        $result = $this->getSmsService()->sendVerifySms($type, $mobile, 0);

        $smsToken = $this->getTokenService()->makeToken($type, array(
            'times'    => 5,
            'duration' => 60 * 30,
            'userId'   => 0,
            'data'     => array(
                'code' => $result['captcha_code'],
                'mobile'   => $mobile
            )
        ));
        return array(
            'smsToken' => $smsToken['token']
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
     * @return \Biz\Sms\Service\SmsService
     */
    private function getSmsService()
    {
        return $this->service('Sms:SmsService');
    }

    /**
     * @return \Biz\User\Service\TokenService
     */
    private function getTokenService()
    {
        return $this->service('User:TokenService');
    }
}