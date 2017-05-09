<?php

namespace ApiBundle\Api\Resource\PayCenter;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Exception\ErrorCode;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Component\Payment\Payment;
use Biz\Order\OrderProcessor\OrderProcessorFactory;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class PayCenter extends AbstractResource
{
    public function add(ApiRequest $request)
    {
        $params = $request->request->all();
        if (empty($params['orderId'])
            || empty($params['targetType'])
            || empty($params['payment'])
            ||!in_array($params['payment'], array('alipay', 'coin')) ) {
            throw new BadRequestHttpException('Missing params', null, ErrorCode::INVALID_ARGUMENT);
        }

        list($checkResult, $order) = $this->service('PayCenter:GatewayService')
            ->beforePayOrder($params['orderId'], $params['targetType'], $params['payment']);

        if ($checkResult) {
            throw new BadRequestHttpException($checkResult['error'], null, $checkResult['code']);
        }

        if ($order['status'] == 'paid') {
            $order['paymentForm'] = array();
            $order['paymentHtml'] = '';
        } else {
            $order['paymentForm'] = $this->generatePaymentForm($order, $request);
            $order['paymentHtml'] = $this->renderView('pay-center/submit-pay-request.html.twig',
                array('form' => $order['paymentForm']));
        }

        return $order;
    }

    private function generatePaymentForm($order, $request)
    {
        $requestParams = array(
            'returnUrl' => $this->generateUrl('pay_return_for_app', array('name' => $order['payment']), true),
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
            throw new BadRequestHttpException('支付模块(支付宝)未开启，请先开启。', null, ErrorCode::INVALID_ARGUMENT);
        }

        if (empty($paymentSetting['alipay_key']) || empty($paymentSetting['alipay_secret'])) {
            throw new BadRequestHttpException('支付模块(支付宝)参数未设置，请先设置。', null, ErrorCode::INVALID_ARGUMENT);
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