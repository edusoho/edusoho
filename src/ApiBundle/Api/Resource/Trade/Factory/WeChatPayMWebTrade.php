<?php

namespace ApiBundle\Api\Resource\Trade\Factory;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class WeChatPayMWebTrade extends BaseTrade
{
    protected $payment = 'wechat';

    protected $platformType = 'Mweb';

    public function getCustomResponse($trade)
    {
        $redirectUrl = $this->generateUrl('cashier_pay_success', array('trade_sn' => $trade['trade_sn']), UrlGeneratorInterface::ABSOLUTE_URL);
        return array(
            'mweb_url' => $trade['platform_created_result']['mweb_url'] . '&redirect_url=' . urlencode($redirectUrl),
        );
    }


}