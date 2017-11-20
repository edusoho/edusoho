<?php

namespace ApiBundle\Api\Resource\Setting;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Exception\ErrorCode;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\OrderFacade\Exception\OrderPayCheckException;
use Biz\OrderFacade\Product\Product;
use Biz\OrderFacade\Service\OrderFacadeService;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class Setting extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function get(ApiRequest $request, $type)
    {
        if (!in_array($type, array('register'))) {
            throw new BadRequestHttpException('Type is error', null, ErrorCode::INVALID_ARGUMENT);
        }

        $method = "get${type}";
        return $this->$method();
    }


    public function getRegister()
    {
        $registerSetting = $this->getSettingService()->get('auth', array('register_mode' => 'closed', 'email_enabled' => 'closed'));
        $registerMode = $registerSetting['register_mode'];
        $isEmailVerifyEnable = $registerSetting['email_enabled'] == 'opened' ? true : false;

        $mode = 'closed';

        switch ($registerMode) {
            case 'closed':
                $mode = 'closed';
                break;
            case 'email':
                $mode = $isEmailVerifyEnable ? 'email_verify' : 'email';
                break;
            case 'mobile':
                $mode = 'mobile';
                break;
            case 'email_or_mobile':
                $mode = $isEmailVerifyEnable ? 'email_verify_mobile': 'email_mobile';
                break;
            default:
                break;
        }

        return array(
            'mode' => $mode
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