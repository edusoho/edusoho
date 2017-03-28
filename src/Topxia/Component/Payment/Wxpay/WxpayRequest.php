<?php
namespace Topxia\Component\Payment\Wxpay;

use Topxia\Component\Payment\Request;
use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\DependencyInjection\SimpleXMLElement;

class WxpayRequest extends Request
{
    protected $unifiedOrderUrl = 'https://api.mch.weixin.qq.com/pay/unifiedorder';
    protected $orderQueryUrl   = 'https://api.mch.weixin.qq.com/pay/orderquery';

    public function form()
    {
        $form['action'] = $this->unifiedOrderUrl . '?_input_charset=utf-8';
        $form['method'] = 'post';
        $form['params'] = $this->convertParams($this->params);
        return $form;
    }

    public function unifiedOrder($openid = null)
    {
        $params = $this->convertParams($this->params, $openid);
        $xml      = $this->toXml($params);
        $response = $this->postRequest($this->unifiedOrderUrl, $xml);
        if (!$response) {
            throw new \RuntimeException('xml数据异常！');
        }
        $response = $this->fromXml($response);
         $this->checkSign($response);
        return $response;
    }

    public function orderQuery()
    {
        $params                    = $this->params;
        $converted                 = array();
        $converted['appid']        = $this->options['appid'];
        $settings                  = $this->getSettingService()->get('payment');
        $converted['mch_id']       = $settings["wxpay_account"];
        $converted['nonce_str']    = $this->getNonceStr();
        $converted['out_trade_no'] = $params['orderSn'];
        $converted['sign']         = $this->signParams($converted);

        $xml      = $this->toXml($converted);
        $response = $this->postRequest($this->orderQueryUrl, $xml);
        $response = $this->fromXml($response);
        $this->checkSign($response);
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
        reset($params);
        $sign = '';

        foreach ($params as $key => $value) {
            if (empty($value)) {
                continue;
            }

            $sign .= $key . '=' . $value . '&';
        }

        $sign = substr($sign, 0, -1);
        $sign .= '&key=' . $this->options['key'];
        return strtoupper(md5($sign));
    }

    /**
     *
     * 检测签名
     */
    public function checkSign($params)
    {
        if (empty($params['sign'])) {
            throw new \RuntimeException("签名错误！");
        }

        $sign = $this->signParams($params);

        if ($params['sign'] == $sign) {
            return true;
        }
        throw new \RuntimeException("签名错误！");
    }

    protected function convertParams($params, $openid = null)
    {
        $converted = array();

        $converted['openid']           = $openid;
        $converted['appid']            = $this->options['appid'];
        $converted['attach']           = '支付';
        $converted['body']             = mb_substr($this->filterText($params['title']), 0, 49, 'utf-8');
        $converted['mch_id']           = $this->options['account'];
        $converted['nonce_str']        = $this->getNonceStr();
        $converted['notify_url']       = $params['notifyUrl'];
        $converted['out_trade_no']     = $params['orderSn'];
        $converted['spbill_create_ip'] = $this->getClientIp();
        $converted['total_fee']        = $this->getAmount($params['amount']);
        $converted['trade_type']       = $this->options['isMicroMessenger'] ? 'JSAPI' : 'NATIVE';
        $converted['product_id']       = $params['orderSn'];
        $converted['sign']             = $this->signParams($converted);
        return $converted;
    }

    protected function getAmount($amount)
    {
        $array = explode('.', $amount);

        if (isset($array[1])) {
            $suffix = $array[1];
            $len    = strlen($suffix);
            for ($i = $len; $i < 2; $i++) {
                $suffix = $suffix . '0';
            }

            $amount = $array[0] . $suffix;
        } else {
            $amount = $amount . '00';
        }

        return intval($amount);
    }


    /**
     *
     * 获取jsapi支付的参数
     * @param array $UnifiedOrderResult 统一支付接口返回的数据
     * @throws WxPayException
     *
     * @return json数据，可直接填入js函数作为参数
     */
    public function getJsApiParameters($UnifiedOrderResult)
    {
        if (!array_key_exists("appid", $UnifiedOrderResult)
            || !array_key_exists("prepay_id", $UnifiedOrderResult)
            || $UnifiedOrderResult['prepay_id'] == ""
        ) {
            throw new \RuntimeException("参数错误");
        }
        $jsApi              = array();
        $timeStamp          = time();
        $jsApi['appId']     = $UnifiedOrderResult["appid"];
        $jsApi['timeStamp'] = "$timeStamp";
        $jsApi['nonceStr']  = $this->getNonceStr();
        $jsApi['package']   = "prepay_id=" . $UnifiedOrderResult['prepay_id'];
        $jsApi['signType']  = "MD5";
        $jsApi['paySign']   = $this->signParams($jsApi);

        return json_encode($jsApi);
    }

    private function toXml($array, $xml = false)
    {
        if (!is_array($array)
            || count($array) <= 0
        ) {
            throw new WxPayException("数组数据异常！");
        }

        $xml = "<xml>";
        foreach ($array as $key => $val) {
            if (is_numeric($val)) {
                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
            } else {
                $xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
            }
        }
        $xml .= "</xml>";
        return $xml;


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
