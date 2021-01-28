<?php

namespace ApiBundle\Api\Resource\Trade\Factory;

class WechatPayAppTrade extends BaseTrade
{
    protected $payment = 'wechat';

    protected $platformType = 'App';

    public function getCustomResponse($trade)
    {
        $platformResult = $trade['platform_created_result'];

        return array(
            'appid' => $platformResult['appid'],
            'partnerid' => $platformResult['mch_id'],
            'prepayid' => $platformResult['prepay_id'],
            'package' => 'Sign=WXPay',
            'noncestr' => $platformResult['nonce_str'],
            'timestamp' => time(),
            'sign' => $platformResult['sign'],
        );
    }
}
