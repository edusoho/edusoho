<?php

namespace ApiBundle\Api\Resource\PayCenter;

use ApiBundle\Api\Exception\ApiException;
use ApiBundle\Api\Exception\InvalidArgumentException;
use ApiBundle\Api\Resource\Resource;
use AppBundle\Component\Payment\Payment;
use Biz\Order\OrderProcessor\OrderProcessorFactory;
use Symfony\Component\HttpFoundation\Request;

class PayCenter extends Resource
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

        if ($order['status'] == 'paid') {
            $order['paymentForm'] = array();
        } else {
            $order['paymentForm'] = $this->generatePaymentForm($order, $request);
        }

        return $order;
    }

    private function generatePaymentForm($order, $request)
    {
        $requestParams = array(
            'returnUrl' => $this->generateUrl('pay_return', array('name' => $order['payment']), true),
            'notifyUrl' => $this->generateUrl('pay_notify', array('name' => $order['payment']), true),
            'showUrl' => $this->generateUrl('pay_success_show', array('id' => $order['id']), true)
        );

        $requestParams['userAgent'] = $request->headers->get('User-Agent');
        $requestParams['isMobile'] = true;
        $paymentRequest = $this->createPaymentRequest($order, $requestParams);
        return $paymentRequest->form();
    }

    private function createPaymentRequest($order, $requestParams)
    {
        $paymentSetting = $this->service('System:SettingService')->get('payment');

        if (empty($paymentSetting['alipay_enabled'])) {
            throw new ApiException('支付模块(支付宝)未开启，请先开启。');
        }

        if (empty($paymentSetting['alipay_key']) || empty($paymentSetting['alipay_secret'])) {
            throw new ApiException('支付模块(支付宝)参数未设置，请先设置。');
        }

        $options = array(
            'key' => $paymentSetting['alipay_key'],
            'secret' => $paymentSetting['alipay_secret'],
            'type' => $paymentSetting['alipay_type'],
        );
        $request = Payment::createRequest($order['payment'], $options);
        $processor = OrderProcessorFactory::create($order['targetType']);
        $targetId = isset($order['targetId']) ? $order['targetId'] : $order['id'];
        $requestParams = array_merge($requestParams, array(
            'orderSn' => $order['sn'],
            'userId' => $order['userId'],
            'title' => $order['title'],
            'targetTitle' => $processor->getTitle($targetId),
            'summary' => '',
            'note' => $processor->getNote($targetId),
            'amount' => $order['amount'],
            'targetType' => $order['targetType'],
        ));

        return $request->setParams($requestParams);
    }
}