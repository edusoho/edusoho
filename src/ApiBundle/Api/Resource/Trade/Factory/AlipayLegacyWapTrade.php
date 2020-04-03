<?php

namespace ApiBundle\Api\Resource\Trade\Factory;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AlipayLegacyWapTrade extends BaseTrade
{
    protected $payment = 'alipay';

    protected $platformType = 'Wap';

    public function getCustomFields($params)
    {
        $customFields = array();
        if (empty($params['wap_pay'])) {
            $customFields['return_url'] = $this->generateUrl('cashier_pay_return_for_app', array('payment' => 'alipay'), UrlGeneratorInterface::ABSOLUTE_URL);
            $customFields['show_url'] = $this->generateUrl('cashier_pay_return_for_app', array('payment' => 'alipay'), UrlGeneratorInterface::ABSOLUTE_URL);
        }

        return $customFields;
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
