<?php

namespace ApiBundle\Api\Resource\Cashier\Trade;

use ApiBundle\Api\Resource\Cashier\BaseTrade;

class AlipayLegacyWapTrade extends BaseTrade
{
    protected $payment = 'alipay';

    protected $platformType = 'Wap';

    public function getCustomFields($params)
    {
        return array(
            'show_url' => $this->generateUrl('cashier_pay_return_for_app', array('payment' => $this->payment), true),
            'return_url' => $this->generateUrl('cashier_pay_return_for_app', array('payment' => $this->payment), true),
        );
    }

}