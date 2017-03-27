<?php
namespace Topxia\Component\Payment\Wxpay;

use Topxia\Component\Payment\Response;
use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\DependencyInjection\SimpleXMLElement;

class WxpayResponse extends Response
{
    protected $orderQueryUrl = 'https://api.mch.weixin.qq.com/pay/orderquery';
    public function getPayData()
    {
        $params          = $this->params;
        $data            = array();
        $data['payment'] = 'wxpay';
        $data['sn']      = $params['out_trade_no'];
        $result          = $this->confirmSellerSendGoods($data['sn']);
        $returnArray     = $this->fromXml($result);
        if ($returnArray['return_code'] != 'SUCCESS' || $returnArray['result_code'] != 'SUCCESS' || $returnArray['trade_state'] != 'SUCCESS') {
            throw new \RuntimeException($this->getServiceKernel()->trans('微信支付失败'));
        }

        if (in_array($returnArray['trade_state'], array('SUCCESS'))) {
            $data['status'] = 'success';
        } elseif (in_array($returnArray['trade_state'], array('CLOSED'))) {
            $data['status'] = 'closed';
        } else {
            $data['status'] = 'unknown';
        }

        $data['amount'] = ((float) $params['total_fee']) / 100;

        if (!empty($params['time_end'])) {
            $data['paidTime'] = strtotime($params['time_end']);
        } else {
            $data['paidTime'] = time();
        }

        return $data;
    }

    private function confirmSellerSendGoods($trade_no)
    {
        $params                    = $this->params;
        $converted                 = array();
        $converted['appid']        = $this->options['appid'];
        $settings                  = $this->getSettingService()->get('payment');
        $converted['mch_id']       = $settings["wxpay_account"];
        $converted['nonce_str']    = $this->getNonceStr();
        $converted['out_trade_no'] = $trade_no;
        $converted['sign']         = strtoupper($this->signParams($converted));

        $xml      = $this->toXml($converted);
        $response = $this->postRequest($this->orderQueryUrl, $xml);
        return $response;
    }

    private function postRequest($url, $params)
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_USERAGENT, 'Topxia Payment Client 1.0');
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
        curl_setopt($curl, CURLOPT_URL, $url);

        curl_setopt($curl, CURLINFO_HEADER_OUT, true);

        $response = curl_exec($curl);

        curl_close($curl);

        return $response;
    }

    public function signParams($params)
    {
        unset($params['sign_type']);
        unset($params['sign']);

        ksort($params);

        $sign = '';

        foreach ($params as $key => $value) {
            if (empty($value)) {
                continue;
            }

            $sign .= $key.'='.$value.'&';
        }

        $sign = substr($sign, 0, -1);
        $sign .= '&key='.$this->options['key'];

        return md5($sign);
    }

    private function toXml($array, $xml = false)
    {
        $simxml = new simpleXMLElement('<!--?xml version="1.0" encoding="utf-8"?--><root></root>');

        foreach ($array as $k => $v) {
            $simxml->addChild($k, $v);
        }

        return $simxml->saveXML();
    }

    private function fromXml($xml)
    {
        $array = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $array;
    }

    private function getNonceStr($length = 32)
    {
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        $str   = "";

        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }

        return $str;
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }
}
