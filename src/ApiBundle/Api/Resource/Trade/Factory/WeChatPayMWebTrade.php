<?php

namespace ApiBundle\Api\Resource\Trade\Factory;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class WeChatPayMWebTrade extends BaseTrade
{
    protected $payment = 'wechat';

    protected $platformType = 'Mweb';

    public function getCustomResponse($trade)
    {
        $redirectUrl = $this->generateUrl('cashier_pay_return', array('payment' => $this->payment), UrlGeneratorInterface::ABSOLUTE_URL);
        return array(
            'mwebUrl' => $trade['platform_created_result']['mweb_url'] . '&redirect_url=' . urlencode($redirectUrl),
        );
    }


}