<?php
namespace Topxia\Component\Payment\Llcbpay;

use Topxia\Component\Payment\Response;
use Topxia\Service\Common\ServiceKernel;

class LlcbpayResponse extends Response
{
    protected $url = 'https://yintong.com.cn/queryapi/orderquery.htm';

    public function getPayData()
    {
        $params = $this->params;
        $error  = $this->hasError();

        if ($error) {
            throw new \RuntimeException(sprintf('网银支付校验失败(%s)。', $error));
        }

        $data['payment'] = 'llcbpay';
        $data['sn']      = $params['no_order'];
        $result= json_decode($this->confirmSellerSendGoods(), true);


        if ($result['result_pay'] == 'SUCCESS') {
            $data['status'] = 'success';
        } else {
            $data['status'] = 'unknown';
        }

        $data['amount']   = $params['money_order'];
        $data['paidTime'] = time();
        $data['raw'] = $params;

        return $data;
    }

    private function hasError()
    {
        if ($this->params['result_pay'] != 'SUCCESS') {
            return '支付异常';
        }

        return false;
    }

    private function confirmSellerSendGoods()
    {
        $params                  = $this->params;
        $data                    = array();
        $data['oid_partner']     = $params['oid_partner'];
        $data['dt_order']        = $params['dt_order'];
        $data['no_order']        = $params['no_order'];
        $data['sign_type']       = $params['sign_type'];
        $data['sign']            = $this->signParams($data);
        $response                = $this->postRequest($this->url, json_encode($data));
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
        ksort($params);
        $sign = '';
        foreach ($params as $key => $value) {
            if (empty($value)) {
                continue;
            }

            $sign .= $key.'='.$value.'&';
        }
        $sign .= 'key='.$this->options['secret'];
        return md5($sign);
    }
}
