<?php

namespace ApiBundle\Api\Resource\Trade\Factory;

use Biz\User\Service\UserService;

class WechatPayJsTrade extends BaseTrade
{
    protected $payment = 'wechat';

    protected $platformType = 'Js';

    public function getCustomFields($params)
    {
        return array(
            'open_id' => $params['openid'],
        );
    }

    /**
     * @return UserService
     */
    private function getUserService()
    {
        return $this->biz->service('User:UserService');
    }
}
