<?php

namespace ApiBundle\Api\Resource\Trade\Factory;

class WeChatPayMWebTrade extends BaseTrade
{
    protected $payment = 'wechat';

    protected $platformType = 'Mweb';

    public function getCustomResponse($trade)
    {
        $returnUrl = urlencode($this->generateUrl('cashier_pay_return_for_h5', array('payment' => 'wechat', 'tradeSn' => $trade['trade_sn']), true));

        return array(
            'mwebUrl' => $trade['platform_created_result']['mweb_url'].'&redirect_url='.$returnUrl,
            'paymentForm' => array(),
            'paymentHtml' => '',
            'paymentUrl' => $this->generateUrl('cashier_wechat_mweb_app_trade', array('tradeSn' => $trade['trade_sn']), true),
        );
    }
}
