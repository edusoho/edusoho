<?php

namespace ApiBundle\Api\Resource\Trade\Factory;

class AlipayLegacyWapTrade extends BaseTrade
{
    protected $payment = 'alipay';

    protected $platformType = 'Wap';

    public function getCustomResponse($trade)
    {
        $platformCreatedResult = $this->getPayService()->getCreateTradeResultByTradeSnFromPlatform($trade['trade_sn']);
        $form = $this->makePayForm($platformCreatedResult);

        return array(
            'paymentForm' => $form,
            'paymentHtml' => $this->renderView('ApiBundle:cashier:submit-pay.html.twig',
                    array('form' => $form)),
            'paymentUrl' => '',
        );
    }

    private function makePayForm($platformCreatedResult)
    {
        $platformCreatedResult['data']['return_url'] = $this->generateUrl('cashier_pay_return_for_app', array('payment' => 'alipay'), true);
        $platformCreatedResult['data']['show_url'] = $this->generateUrl('cashier_pay_return_for_app', array('payment' => 'alipay'), true);
        $form = array();
        $urlParts = explode('?', $platformCreatedResult['url']);
        $form['action'] = $urlParts[0].'?_input_charset=UTF-8';
        $form['method'] = 'post';
        $form['params'] = $platformCreatedResult['data'];

        return $form;
    }
}
