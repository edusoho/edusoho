<?php
namespace Topxia\Component\Payment\Wxpay;

use Topxia\Component\Payment\Request;
use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\DependencyInjection\SimpleXMLElement;

class WxpayTradeQueryRequest extends Request
{
    protected $unifiedOrderUrl = 'https://api.mch.weixin.qq.com/pay/unifiedorder';
    protected $orderQueryUrl   = 'https://api.mch.weixin.qq.com/pay/orderquery';

    public function form()
    {
        $params         = array();
        $form['action'] = $this->unifiedOrderUrl.'?_input_charset=utf-8';
        $form['method'] = 'post';
        $form['params'] = $this->convertParams($this->params);
        return $form;
    }

    public function tradeQuery()
    {
        $params             = $this->params;
        $converted          = array();
        $converted['appid'] = $this->options['key'];

        $settings                  = $this->getSettingService()->get('payment');
        $converted['mch_id']       = $settings["wxpay_account"];
        $converted['nonce_str']    = $this->getNonceStr();
        $converted['out_trade_no'] = $params['sn'];
        $converted['sign']         = strtoupper($this->signParams($converted));

        $xml      = $this->toXml($converted);
        $response = $this->postRequest($this->orderQueryUrl, $xml);
        return $response;
    }

    public function fromXml($xml)
    {
        $array = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $array;
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
        $sign .= '&key='.$this->options['secret'];

        return md5($sign);
    }

    protected function convertParams($params)
    {
        $converted = array();

        $converted['appid']            = $this->options['key'];
        $converted['attach']           = '支付';
        $converted['body']             = mb_substr($this->filterText($params['title']), 0, 49, 'utf-8');
        $settings                      = $this->getSettingService()->get('payment');
        $converted['mch_id']           = $settings["wxpay_account"];
        $converted['nonce_str']        = $this->getNonceStr();
        $converted['notify_url']       = $params['notifyUrl'];
        $converted['out_trade_no']     = $params['orderSn'];
        $converted['spbill_create_ip'] = $this->getClientIp();
        $converted['total_fee']        = intval($params['amount'] * 100);
        $converted['trade_type']       = 'NATIVE';
        $converted['product_id']       = $params['orderSn'];
        $converted['sign']             = strtoupper($this->signParams($converted));

        return $converted;
    }

    private function toXml($array, $xml = false)
    {
        $simxml = new simpleXMLElement('<!--?xml version="1.0" encoding="utf-8"?--><root></root>');

        foreach ($array as $k => $v) {
            $simxml->addChild($k, $v);
        }

        return $simxml->saveXML();
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

    protected function filterText($text)
    {
        return str_replace(array('#', '%', '&', '+'), array('＃', '％', '＆', '＋'), $text);
    }

    private function getPaymentType()
    {
        return empty($this->options['type']) ? 'direct' : $this->options['type'];
    }

    protected function setting($name, $default = null)
    {
        return $this->get('topxia.twig.web_extension')->getSetting($name, $default);
    }

    protected function getServiceKernel()
    {
        return ServiceKernel::instance();
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }
}
