<?php

namespace ApiBundle\Api\Resource\Trade\Factory;

class WeChatPayMiniAppTrade extends BaseTrade
{
    protected $payment = 'wechat_app';

    protected $platformType = 'Js';

    public function getCustomFields($params)
    {
        return array(
            'open_id' => $params['openid'],
        );
    }
}
