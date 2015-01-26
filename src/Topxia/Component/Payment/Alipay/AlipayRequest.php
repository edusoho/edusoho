<?php
namespace Topxia\Component\Payment\Alipay;

use Topxia\Component\Payment\Request;

class AlipayRequest extends Request {

    protected $url = 'https://mapi.alipay.com/gateway.do';

    public function form()
    {
        $form = array();
        $form['action'] = $this->url . '?_input_charset=utf-8';
        $form['method'] = 'post';
        $form['params'] = $this->convertParams($this->params);
        return $form;
    }

    public function signParams($params) {
        unset($params['sign_type']);
        unset($params['sign']);

        ksort($params);

        $sign = '';
        foreach ($params as $key => $value) {
            if (empty($value)) {
                continue;
            }
            $sign .= $key . '=' . $value . '&';
        }
        $sign = substr($sign, 0, - 1);
        $sign .=$this->options['secret'];

        return md5($sign);
    }

    protected function convertParams($params)
    {
        $converted = array();

        if ($this->getPaymentType() == 'dualfun') {
            $converted['service'] = 'trade_create_by_buyer';
        } elseif ($this->getPaymentType() == 'escow') {
            $converted['service'] = 'create_partner_trade_by_buyer';
        } else {
            $converted['service'] = 'create_direct_pay_by_user';
        }

        $converted['partner'] = $this->options['key'];
        $converted['payment_type'] = 1;
        $converted['_input_charset'] = 'utf-8';
        $converted['sign_type'] = 'MD5';
        $converted['out_trade_no'] = $params['orderSn'];
        $converted['subject'] = $this->filterText($params['title']);
        $converted['seller_id'] = $this->options['key'];

        if (in_array($this->getPaymentType(), array('dualfun', 'escow'))) {
            $converted['price'] = $params['amount'];
            $converted['quantity'] = 1;
            $converted['logistics_type'] = 'POST';
            $converted['logistics_fee'] = '0.00';
            $converted['logistics_payment'] = 'BUYER_PAY';
        } else {
            $converted['total_fee'] = $params['amount'];
        }

        if (!empty($params['notifyUrl'])) {
            $converted['notify_url'] = $params['notifyUrl'];
        }

        if (!empty($params['returnUrl'])) {
            $converted['return_url'] = $params['returnUrl'];
        }

        if (!empty($params['showUrl'])) {
            $converted['show_url'] = $params['showUrl'];
        }

        if (!empty($params['summary'])) {
            $converted['body'] = $this->filterText($params['summary']);
        }

        $converted['sign'] = $this->signParams($converted);

        return $converted;
    }

    protected function filterText($text)
    {
        return str_replace(array('#', '%', '&', '+'), array('＃', '％', '＆', '＋'), $text);
    }

    private function getPaymentType()
    {
        return empty($this->options['type']) ? 'direct' : $this->options['type'];
    }

}