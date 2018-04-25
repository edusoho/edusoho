<?php

namespace Codeages\Biz\Pay\Payment;

use Codeages\Biz\Framework\Service\Exception\AccessDeniedException;
use Codeages\Biz\Framework\Util\ArrayToolkit;
use Codeages\Biz\Framework\Service\Exception\InvalidArgumentException;
use Omnipay\Omnipay;

class AlipayGateway extends AbstractGateway
{
    public function createTrade($data)
    {
        if (!ArrayToolkit::requireds($data, array(
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

        $platformType = empty($data['platform_type']) ? 'Web' : $data['platform_type'];

        if (!in_array($platformType, array('Web', 'Wap'))) {
            throw new InvalidArgumentException("platform_type is invalid, it must be 'web' or 'wap'.");
        }

        $gateway = $this->createGateway($platformType);
        $gateway->setReturnUrl($data['return_url']);
        $gateway->setNotifyUrl($data['notify_url']);

        $method = "make{$platformType}Order";
        $order = $this->$method($data);

        $response = $gateway->purchase($order)->send();

        return array(
            'url' => $response->getRedirectUrl(),
            'data' => $response->getRedirectData(),
        );
    }

    public function closeTrade($trade)
    {
        $platformType = empty($trade['platform_type']) ? 'Web' : $trade['platform_type'];
        $gateway = $this->createGateway($platformType);

        $response = $gateway->close(array(
            'out_trade_no' => $trade['trade_sn'],
        ))->send();

        return $response;
    }

    protected function makeWebOrder($data)
    {
        $order = array();
        $order['subject'] = $data['goods_title'];
        $order['body'] = $data['goods_detail'];
        $order['out_trade_no'] = $data['trade_sn'];
        $order['total_fee'] = $data['amount'] / 100;
        $order['exter_invoke_ip'] = $data['create_ip'];

        $order['extra_common_param'] = json_encode($data['attach']);

        return $order;
    }

    protected function makeWapOrder($data)
    {
        $order = array();
        $order['subject'] = $data['goods_title'];
        $order['body'] = $data['goods_detail'];
        $order['out_trade_no'] = $data['trade_sn'];
        $order['total_fee'] = $data['amount'] / 100;
        $order['app_pay'] = isset($data['app_pay']) ? $data['app_pay'] : '';
        $order['show_url'] = isset($data['show_url']) ? $data['show_url'] : '';

        $order['passback_params'] = urlencode(json_encode($data['attach']));

        return $order;
    }

    public function converterNotify($data)
    {
        $platformType = $data['platform_type'];
        unset($data['platform_type']);
        $gateway = $this->createGateway($platformType);
        $request = $gateway->completePurchase();
        $request->setParams($data);

        $response = $request->send();

        if ($response->isPaid()) {
            $method = "get{$platformType}PaidNotifyData";

            return array(
                $this->$method($data),
                'success',
            );
        }

        return array(
            array(
                'status' => 'failture',
                'notify_data' => $data,
            ),
            'fail',
        );
    }

    protected function getWapPaidNotifyData($data)
    {
        return array(
            'status' => 'paid',
            'cash_flow' => $data['trade_no'],
            'paid_time' => $this->getPaidTime($data),
            'pay_amount' => (int) ($data['total_fee'] * 1000 / 10),
            'cash_type' => 'RMB',
            'trade_sn' => $data['out_trade_no'],
            'attach' => !empty($data['extra_common_param']) ? json_decode($data['extra_common_param'], true) : array(),
            'notify_data' => $data,
        );
    }

    protected function getWebPaidNotifyData($data)
    {
        return array(
            'status' => 'paid',
            'cash_flow' => $data['trade_no'],
            'paid_time' => $this->getPaidTime($data),
            'pay_amount' => (int) ($data['total_fee'] * 1000 / 10),
            'cash_type' => 'RMB',
            'trade_sn' => $data['out_trade_no'],
            'attach' => !empty($data['extra_common_param']) ? json_decode($data['extra_common_param'], true) : array(),
            'notify_data' => $data,
        );
    }

    protected function getPaidTime($data)
    {
        if (!empty($data['gmt_payment'])) {
            return strtotime($data['gmt_payment']);
        }

        return strtotime($data['notify_time']);
    }

    protected function createGateway($platformType = 'Web')
    {
        $ominpayType = 'Web' == $platformType ? 'LegacyExpress' : 'LegacyWap';

        $config = $this->getSetting();
        $gateway = Omnipay::create("Alipay_{$ominpayType}");
        $gateway->setSellerEmail($config['seller_email']);
        $gateway->setSellerId($config['partner']);
        $gateway->setPartner($config['partner']);
        $gateway->setKey($config['key']);

        return $gateway;
    }

    protected function getSetting()
    {
        $config = $this->biz['payment.platforms']['alipay'];

        return array(
            'seller_email' => $config['seller_email'],
            'partner' => $config['partner'],
            'key' => $config['key'],
        );
    }

    public function applyRefund($data)
    {
        throw new AccessDeniedException('can not apply refund with alipay.');
    }

    public function queryTrade($tradeSn)
    {
        return null;
    }

    public function converterRefundNotify($data)
    {
        throw new AccessDeniedException('can not convert refund notify with alipay.');
    }
}
