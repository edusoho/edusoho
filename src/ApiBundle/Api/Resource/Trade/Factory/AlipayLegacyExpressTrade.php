<?php

namespace ApiBundle\Api\Resource\Trade\Factory;

class AlipayLegacyExpressTrade extends BaseTrade
{
    protected $payment = 'alipay';

    protected $platformType = 'Web';

    public function createResponse($trade)
    {
        return array(
            'tradeSn' => $trade['trade_sn'],
            'redirectUrl' => $this->generateUrl('cashier_redirect', array('tradeSn' => $trade['trade_sn'])),
        );
    }
}