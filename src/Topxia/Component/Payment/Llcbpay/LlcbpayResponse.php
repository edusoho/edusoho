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
        $error  = $this->hasError($params);

        if ($error) {
            throw new \RuntimeException(sprintf('网银支付校验失败(%s)。', $error));
        }

        $data['payment'] = 'Llcbpay';
        $data['sn']      = $params['no_order'];
        $result= $this->confirmSellerSendGoods();

        if (in_array($result['result_pay'], array('WAITING', 'PROCESSING'))) {
            return array('sn' => $params['no_order'], 'status' => 'waitBuyerConfirmGoods');
        } elseif ($result['result_pay'] == 'SUCCESS') {
            $data['status'] = 'success';
        } else {
            $data['status'] = 'unknown';
        }

        $data['amount']   = $params['pay_amt'];
        $data['paidTime'] = time();

        $data['raw'] = $params;

        return $data;
    }

    private function hasError($params)
    {
        if ($params['result_pay'] != 'SUCCESS') {
            return '网银支付异常';
        }
        return false;
    }

    private function confirmSellerSendGoods()
    {
        $params                  = $this->params;
        $data                    = array();
        $data['oid_partner']     = $params['oid_partner'];
        $data['sign_type']      　= "MD5";
        $data['no_order']        = $params['no_order'];
        $data['dt_orde']         = date("YmdHis", time());
        $data['sign']            = $this->signParams($data);
        $response                = $this->postRequest($this->url, $data);
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

    private function signParams($params)
    {
        ksort($params);
        $sign   = '';
        foreach ($params as $key => $value) {
            if (empty($value)) {
                continue;
            }

            $sign .= $key.'='.strtolower($value).'&';
        }

        $sign .= 'key='.$this->options['secret'];
        return md5($sign);
    }
}
