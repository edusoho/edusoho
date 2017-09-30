<?php

namespace ApiBundle\Api\Resource\Cashier\Trade;

use ApiBundle\Api\Resource\Cashier\BaseTrade;

class WechatPayJsTrade extends BaseTrade
{
    protected $payment = 'wechat';

    protected $platformType = 'Js';

    public function getCustomFields($params)
    {
        return array(
            'open_id' => $params['openId'],
        );
    }

}