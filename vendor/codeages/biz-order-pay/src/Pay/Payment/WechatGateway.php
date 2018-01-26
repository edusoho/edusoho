<?php

namespace Codeages\Biz\Pay\Payment;

use Codeages\Biz\Framework\Util\ArrayToolkit;
use Codeages\Biz\Framework\Service\Exception\InvalidArgumentException;
use Codeages\Biz\Pay\Exception\PayGatewayException;
use Omnipay\Omnipay;

class WechatGateway extends AbstractGateway
{
    public function converterNotify($data)
    {
        $gateway = $this->createGateway('WechatPay');
        $request = $gateway->completePurchase(array(
            'request_params' => $data
        ));
        $response = $request->send();
        $data = $request->getData();

        if ($response->isPaid()) {
            return array(
                $this->converterTradeResponse($data),
                $this->getNotifyResponse()
            );
        }

        return array(
            array(
                'status' => 'failture',
                'notify_data' => $data,
            ),
            $this->getNotifyResponse()
        );
    }

    public function queryTrade($tradeSn)
    {
        $response = $this->createGateway("WechatPay")->query(array('out_trade_no' => $tradeSn))->send();
        $data = $response->getData();
        if ($response->isSuccessful() && $data['trade_state'] == 'SUCCESS') {
            $result = $this->converterTradeResponse($data);
            return $result;
        }
    }

    protected function converterTradeResponse($data)
    {
        return array(
            'status' => 'paid',
            'cash_flow' => $data['transaction_id'],
            'paid_time' => $this->timeConverter($data['time_end']),
            'pay_amount' => $data['cash_fee'],
            'cash_type' => $data['fee_type'],
            'trade_sn' => $data['out_trade_no'],
            'attach' => json_decode($data['attach'], true),
            'notify_data' => $data,
        );
    }

    protected function timeConverter($time)
    {
        $year = substr($time, 0, 4);
        $month = substr($time, 4, 2);
        $day = substr($time, 6, 2);
        $hour = substr($time, 8, 2);
        $min = substr($time, 10, 2);
        $sec = substr($time, 12, 2);
        return strtotime("{$year}-{$month}-{$day} {$hour}:{$min}:{$sec}");
    }

    protected function getNotifyResponse()
    {
        return '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
    }

    public function createTrade($data)
    {
        if (!ArrayToolkit::requireds($data, array(
            'platform_type',
            'goods_title',
            'goods_detail',
            'attach',
            'trade_sn',
            'amount',
            'notify_url',
            'create_ip',
        ))) {
            throw new InvalidArgumentException('trade args is invalid.');
        }

        if (!empty($data['platform_type']) && 'Js' == $data['platform_type'] && empty($data['open_id'])) {
            throw new InvalidArgumentException('trade args is invalid.');
        }

        $platformType = ucfirst($data['platform_type']);
        $gateway = $this->createGateway("WechatPay_{$platformType}");

        $order['body'] = $data['goods_title'];
        $order['detail'] = $data['goods_detail'];
        $order['attach'] = json_encode($data['attach']);
        $order['out_trade_no'] = $data['trade_sn'];
        $order['total_fee'] = $data['amount'];
        $order['notify_url'] = $data['notify_url'];
        $order['spbill_create_ip'] = $data['create_ip'];
        $order['fee_type'] = 'CNY';
        if ($data['platform_type'] == 'Js') {
            $order['open_id'] = $data['open_id'];
        }

        $request  = $gateway->purchase($order);
        $response = $request->send();

        if ($response->isSuccessful()) {
            if ($data['platform_type'] == 'Js') {
                return $response->getJsOrderData();
            } else {
                return $response->getData();
            }
        } else {
            $data = $response->getData();
            throw new PayGatewayException($data['return_msg']);
        }


    }

    public function applyRefund($trade)
    {
        $gateway = $this->createGateway("WechatPay");

        $response = $gateway->refund(array(
            'transaction_id' => $trade['platform_sn'],
            'out_trade_no' => $trade['trade_sn'],
            'out_refund_no' => time(),
            'total_fee' => $trade['cash_amount'],
            'refund_fee' => $trade['cash_amount'],
            'refund_desc' => empty($trade['refund_reason']) ? '' : $trade['refund_reason']
        ))->send();

        return $response;
    }

    public function closeTrade($trade)
    {
        $gateway = $this->createGateway("WechatPay");

        $response = $gateway->close(array(
            'out_trade_no' => $trade['trade_sn']
        ))->send();

        return $response;
    }

    public function converterRefundNotify($data)
    {
        $gateway = $this->createGateway('WechatPay');
        $request = $gateway->completeRefund(array(
            'request_params' => $data
        ));
        $response = $request->send();
        $data = $request->getData();
        $reqInfo = $data['req_info'];
        if ($response->isRefunded()) {
            return array(
                array(
                    'status' => 'refunded',
                    'cash_flow' => $reqInfo['transaction_id'],
                    'refund_time' => $this->timeConverter($reqInfo['success_time']),
                    'pay_amount' => $reqInfo['refund_fee'],
                    'trade_sn' => $reqInfo['out_trade_no'],
                    'refund_sn' => $reqInfo['out_refund_no'],
                    'notify_data' => $data,
                ),
                $this->getNotifyResponse()
            );
        }

        return array(
            array(
                'status' => 'failture',
                'notify_data' => $data,
            ),
            $this->getNotifyResponse()
        );
    }

    protected function createGateway($type)
    {
        $config = $this->getSetting();
        $gateway = Omnipay::create($type);
        $gateway->setAppId($config['appid']);
        $gateway->setMchId($config['mch_id']);
        $gateway->setApiKey($config['key']);
        $gateway->setCertPath($config['cert_path']);
        $gateway->setKeyPath($config['key_path']);
        return $gateway;
    }

    protected function getSetting()
    {
        $config = $this->biz['payment.platforms']['wechat'];
        return array(
            'appid' => $config['appid'],
            'mch_id' => $config['mch_id'],
            'key' => $config['key'],
            'cert_path' => $config['cert_path'],
            'key_path' => $config['key_path'],
        );
    }
}