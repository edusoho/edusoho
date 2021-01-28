<?php

namespace ApiBundle\Api\Resource\Trade\Factory;

use AppBundle\Common\MathToolkit;

class WechatPayNativeTrade extends BaseTrade
{
    protected $payment = 'wechat';

    protected $platformType = 'Native';

    public function getCustomResponse($trade)
    {
        return array(
            'cash_amount' => round(MathToolkit::simple($trade['cash_amount'], 0.01), 2),
            'qrcodeUrl' => $this->generateUrl('common_qrcode', array('text' => $trade['platform_created_result']['code_url'])),
            'successUrl' => $this->generateUrl('cashier_pay_success', array('trade_sn' => $trade['trade_sn'])),
        );
    }
}
