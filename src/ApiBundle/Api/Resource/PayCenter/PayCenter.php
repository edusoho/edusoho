<?php

namespace ApiBundle\Api\Resource\Order;

use ApiBundle\Api\Exception\ApiException;
use ApiBundle\Api\Exception\InvalidArgumentException;
use ApiBundle\Api\Resource\Resource;
use Symfony\Component\HttpFoundation\Request;

class Order extends Resource
{
    public function add(Request $request)
    {
        $params = $request->request->all();
        if (empty($params['orderId'])
            || empty($params['payment'])
            ||!in_array($params['payment'], array('alipay', 'coin')) ) {
            throw new InvalidArgumentException();
        }

        list($checkResult, $order) = $this->service('PayCenter:GatewayService')
            ->beforePayOrder($params['orderId'], $params['payment']);

        if ($checkResult) {
            throw new ApiException($checkResult['error'], $checkResult['code']);
        }

        if ($order['status'] !== 'paid') {
            $order['paymentUrl'] = '';
        } else {
            $order['paymentUrl'] = $this->generatePaymentUrl($order);
        }

        return $order;
    }

    private function generatePaymentUrl($order)
    {
        $requestParams = array(
            'returnUrl' => $this->generateUrl('pay_return', array('name' => $order['payment']), true),
            'notifyUrl' => $this->generateUrl('pay_notify', array('name' => $order['payment']), true),
            'showUrl' => $this->generateUrl('pay_success_show', array('id' => $order['id']), true)
        );
    }
}