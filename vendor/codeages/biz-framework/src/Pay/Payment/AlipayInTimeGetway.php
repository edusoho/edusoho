<?php

namespace Codeages\Biz\Framework\Pay\Payment;

use Codeages\Biz\Framework\Util\ArrayToolkit;
use Codeages\Biz\Framework\Service\Exception\InvalidArgumentException;
use Omnipay\Omnipay;

class AlipayInTimeGetway extends AbstractGetway
{
    public function createTrade($data)
    {
        if (ArrayToolkit::requireds($data, array(
            'goods_title',
            'goods_detail',
            'attach',
            'trade_sn',
            'amount',
            'notify_url',
            'return_url',
            'create_ip',
        ))) {
            throw new InvalidArgumentException('trade args is invalid.');
        }

        $gateway = $this->createGetWay();
        $gateway->setReturnUrl($data['notify_url']);
        $gateway->setNotifyUrl($data['return_url']);

        $order = array();
        $order['subject'] = $data['goods_title'];
        $order['body'] = $data['goods_detail'];
        $order['extra_common_param'] = json_encode($data['attach']);
        $order['out_trade_no'] = $data['trade_sn'];
        $order['total_fee'] = $data['amount']/100;
        $order['exter_invoke_ip'] = $data['create_ip'];

        $response = $gateway->purchase($order)->send();

        $url = $response->getRedirectUrl();
        return $url;
    }

    public function converterNotify($data)
    {
        $gateway = $this->createGetWay();
        $request = $gateway->completePurchase();
        $request->setParams($data);

        $response = $request->send();

        if ($response->isPaid()) {
            return array(
                array(
                    'status' => 'paid',
                    'cash_flow' => $data['trade_no'],
                    'paid_time' => strtotime($data['gmt_payment']),
                    'pay_amount' => (int)($data['total_fee']*100),
                    'cash_type' => 'RMB',
                    'trade_sn' => $data['out_trade_no'],
                    'attach' => json_decode($data['extra_common_param'], true),
                    'notify_data' => $data,
                ),
                'success'
            );
        }

        return array(
            array(
                'status' => 'failture',
                'notify_data' => $data,
            ),
            'fail'
        );
    }

    protected function createGetWay()
    {
        $config = $this->getSetting();
        $gateway = Omnipay::create('Alipay_LegacyExpress');
        $gateway->setSellerEmail($config['seller_email']);
        $gateway->setPartner($config['partner']);
        $gateway->setKey($config['key']);
        return $gateway;
    }

    protected function getSetting()
    {
        $config = $this->biz['payment.alipay.in_time'];
        return array(
            'seller_email' => $config['seller_email'],
            'partner' => $config['partner'],
            'key' => $config['key'],
        );
    }

    public function applyRefund($data)
    {
        // TODO: Implement applyRefund() method.
    }

    public function converterRefundNotify($data)
    {
        // TODO: Implement converterRefundNotify() method.
    }
}