<?php

namespace ApiBundle\Api\Resource\Trade\Factory;

use AppBundle\Common\MathToolkit;

class WeChatPayMWebTrade extends BaseTrade
{
    protected $payment = 'wechat';

    protected $platformType = 'Mweb';

    public function getCustomResponse($trade)
    {
        return array(
            'mweb_url' => $trade['platform_created_result']['mweb_url'],
            'cash_amount' => round(MathToolkit::simple($trade['cash_amount'], 0.01), 2),
            'successUrl' => $this->generateUrl('cashier_pay_success', array('trade_sn' => $trade['trade_sn'])),
        );
    }


}