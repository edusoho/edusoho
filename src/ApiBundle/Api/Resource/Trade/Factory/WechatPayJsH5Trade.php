<?php

namespace ApiBundle\Api\Resource\Trade\Factory;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class WechatPayJsH5Trade extends BaseTrade
{
    protected $payment = 'wechat';

    protected $platformType = 'Js';

    public function getCustomFields($params)
    {
        return array(
            'open_id' => $params['openid'],
        );
    }

    public function getCustomResponse($trade)
    {
        $result = array(
            'platformCreatedResult' => $trade['platform_created_result'],
            'paidSuccessUrlH5' => $this->generateUrl('cashier_pay_success_for_h5', array('trade_sn' => $trade['trade_sn']), UrlGeneratorInterface::ABSOLUTE_URL),
        );

        return $result;
    }
}
