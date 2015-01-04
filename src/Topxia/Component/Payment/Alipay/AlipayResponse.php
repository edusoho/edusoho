<?php
namespace Topxia\Component\Payment\Alipay;

use Topxia\Component\Payment\Response;

class AlipayResponse extends Response
{

    protected $url = 'https://mapi.alipay.com/gateway.do';
    
    public function getPayData()
    {
        $error = $this->hasError();
        if ($error) {
            throw new \RuntimeException(sprintf('支付宝支付校验失败(%s)。', $error));
        }

        $params = $this->params;

        if ($params['trade_status'] == 'WAIT_SELLER_SEND_GOODS') {

            $trade_no = $params['trade_no'];
            $result = $this->confirmSellerSendGoods($trade_no);
            
            if ($result == "WAIT_BUYER_CONFIRM_GOODS") {
                return array('sn' => $params['trade_no'], 'status' => 'waitBuyerConfirmGoods');
            }
        }

        if($params['trade_status'] == "WAIT_BUYER_CONFIRM_GOODS") {
           return array('sn' => $params['trade_no'], 'status' => 'waitBuyerConfirmGoods');
        }

        $data = array();
        $data['payment'] = 'alipay';
        $data['sn'] = $params['out_trade_no'];
        $data['status'] = in_array($params['trade_status'], array('TRADE_SUCCESS', 'TRADE_FINISHED')) ? 'success' : 'unknown';
        $data['amount'] = $params['total_fee'];

        if (!empty($params['gmt_payment'])) {
            $data['paidTime'] = strtotime($params['gmt_payment']);
        } elseif (!empty($params['notify_time'])) {
            $data['paidTime'] = strtotime($params['notify_time']);
        } else {
            $data['paidTime'] = time();
        }

        $data['raw'] = $params;

        return $data;
    }

    private function hasError()
    {
        $sign = $this->signParams($this->params);

        if ($this->params['sign'] !== $sign) {
            return 'sign_error';
        }
        if(!empty($this->params['notify_id'])){
            $notifyResult = $this->getRequest('https://mapi.alipay.com/gateway.do', array(
                'notify_id' => $this->params['notify_id'],
                'service' => 'notify_verify',
                'partner' => $this->options['key'],
            ));

            if (strtolower($notifyResult) !== 'true') {
                return 'notify_verify_error';
            }
        }
        
        return false;
    }

    private function confirmSellerSendGoods($trade_no)
    {
        $params = array();
        $params['service'] = "send_goods_confirm_by_platform";
        $params['partner'] =  $this->options['key'];
        $params['_input_charset'] = "utf-8";
        $params['sign_type'] = "MD5";
        $params['trade_no'] = $trade_no;
        $params['transport_type'] = "DIRECT";
        $params['sign'] = $this->signParams($params);

        $html_text = $this->postRequest($this->url,$params);
        $doc = new \DOMDocument('1.0', 'UTF-8');
        $doc->loadXML($html_text);

        if( ! empty($doc->getElementsByTagName( "alipay" )->item(0)->nodeValue) ) {
            $trade_status = $doc->getElementsByTagName( "trade_status" )->item(0)->nodeValue;
            return $trade_status;
        } else {
            return null;
        }
    }

    private function postRequest($url, $params)
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_USERAGENT, 'Topxia Payment Client 1.0');
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
        curl_setopt($curl, CURLOPT_URL, $url );

        curl_setopt($curl, CURLINFO_HEADER_OUT, TRUE );

        $response = curl_exec($curl);

        curl_close($curl);

        return $response;
    }

    private function signParams($params) {
        unset($params['sign_type']);
        unset($params['sign']);

        ksort($params);
        $params = array_filter($params);

        $sign = '';
        foreach ($params as $key => $value) {
            $sign .= $key . '=' . $value . '&';
        }
        $sign = substr($sign, 0, - 1);
        $sign .=$this->options['secret'];
        return md5($sign);
    }

}