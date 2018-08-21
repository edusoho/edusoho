<?php

namespace ApiBundle\Api\Resource\Trade\Factory;

class AlipayLegacyWapTrade extends BaseTrade
{
    protected $payment = 'alipay';

    protected $platformType = 'Wap';

    public function getCustomFields($params)
    {
        if (empty($params['wap_pay'])) {
            $params['return_url'] = $this->generateUrl('cashier_pay_return_for_app', array('payment' => 'alipay'), true);
            $params['show_url'] = $this->generateUrl('cashier_pay_return_for_app', array('payment' => 'alipay'), true);
        }
        unset($params['wap_pay']);

        return $params;
    }

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
        $form = array();
        $urlParts = explode('?', $platformCreatedResult['url']);
        $form['action'] = $urlParts[0].'?_input_charset=UTF-8';
        $form['method'] = 'post';
        $form['params'] = $platformCreatedResult['data'];

        return $form;
    }
}
