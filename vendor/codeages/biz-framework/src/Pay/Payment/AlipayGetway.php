<?php

namespace Codeages\Biz\Framework\Pay\Payment;

use Codeages\Biz\Framework\Util\ArrayToolkit;
use Codeages\Biz\Framework\Service\Exception\InvalidArgumentException;
use Omnipay\Omnipay;

class AlipayGetway extends AbstractGetway
{

    static $getway = 'https://mapi.alipay.com/gateway.do';

    public function createTrade($data)
    {
        $config = $this->getSetting();

        $params = array(
            'service' => 'create_direct_pay_by_user',
            'partner' => $config['partner'],
            'seller_email' => $config['seller_email'],
            'seller_id' => '',
            'seller_account_name' => '',
            '_input_charset' => 'UTF-8',
            'sign_type' => 'MD5',
            'sign' => '',
            'notify_url' => '',
            'return_url' => '',
            'out_trade_no' => '',
            'subject' => '',
            'body' => '',
            'payment_type' => '1', //只支持取值为1（商品购买）。
            'total_fee' => '',
            'price' => '',
            'quantity' => '',
            'exter_invoke_ip' => '',
            'extra_common_param' => '',
            'buyer_id' => '',
            'buyer_email' => '',
            'buyer_account_name' => '',
            'it_b_pay' => '',  //支付超时时间
            'show_url' => '',
            'enable_paymethod' => '',
            'disable_paymethod' => '',
            'anti_phishing_key' => '',
            'token' => '',
            'qr_pay_mode' => '',
            'qrcode_width' => '',
            'need_buyer_realnamed' => '',
            'hb_fq_param' => '',
            'goods_type' => '',
            'extend_param' => '',
        );

        $keys = array_keys($params);

        $data = ArrayToolkit::parts($data, $params);


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

        $gateway = $this->createGetWay();
        $gateway->setReturnUrl($data['return_url']);
        $gateway->setNotifyUrl($data['notify_url']);

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

    protected function sign($params)
    {
        $originParams = $params;
        unset($params['sign']);
        unset($params['sign_type']);


    }

    protected function sign($params, $signType)
    {
        $signer = new Signer($params);

        $signType = strtoupper($signType);

        if ($signType == 'MD5') {
            if (! $this->getKey()) {
                throw new InvalidRequestException('The `key` is required for `MD5` sign_type');
            }

            $sign = $signer->signWithMD5($this->getKey());
        } elseif ($signType == 'RSA') {
            if (! $this->getPrivateKey()) {
                throw new InvalidRequestException('The `private_key` is required for `RSA` sign_type');
            }

            $sign = $signer->signWithRSA($this->getPrivateKey());
        } else {
            throw new InvalidRequestException('The signType is not allowed');
        }

        return $sign;
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
                    'paid_time' => !empty($data['gmt_payment']) ? strtotime($data['gmt_payment']) : strtotime($data['notify_time']),
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
        $config = $this->biz['payment.platforms']['alipay.in_time'];
        return array(
            'seller_email' => $config['seller_email'],
            'partner' => $config['partner'],
            'key' => $config['key'],
        );
    }

    public function queryTrade($trade)
    {

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