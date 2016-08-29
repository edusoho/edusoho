<?php

namespace Topxia\Component\Payment\Llcbpay;

use Topxia\Component\Payment\Request;
use Topxia\Service\Common\ServiceKernel;

class LlcbpayRequest extends Request
{
    protected $url = 'https://yintong.com.cn/payment/bankgateway.htm';

    public function form()
    {
        $form = array();
        $form['action'] = $this->url;
        $form['method'] = 'post';
        $form['params'] = $this->convertParams($this->params);

        return $form;
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

    protected function convertParams($params)
    {
        $converted = array();
        $converted['busi_partner'] = '101001';
        $converted['dt_order'] = date('YmdHis', time());
        $converted['money_order'] = $params['amount'];
        $converted['name_goods'] = mb_substr($this->filterText($params['title']), 0, 12, 'utf-8');
        $converted['no_order'] = $params['orderSn'];
        if (!empty($params['notifyUrl'])) {
            $converted['notify_url'] = $params['notifyUrl'];
        }
        $converted['oid_partner'] = $this->options['key'];
        $converted['sign_type'] = 'MD5';
        $converted['version'] = '1.0';
        $identify = $this->getSettingService()->get('llpay_identify');
        if (!$identify) {
            $identify = $this->getIdentify();
        }
        $converted['user_id']      = $identify."_".$params['userId'];
        $converted['timestamp'] = date('YmdHis', time());
        if (!empty($params['returnUrl'])) {
            $converted['url_return'] = $params['returnUrl'];
        }

        $converted['userreq_ip'] = str_replace('.', '_', $this->getClientIp());
        $converted['bank_code'] = '';
        $converted['pay_type'] = '1';
        $converted['sign'] = $this->signParams($converted);

        return $converted;
    }

    protected function filterText($text)
    {
        preg_match_all('/[\x{4e00}-\x{9fa5}A-Za-z0-9.]*/iu', $text, $results);
        $title = '';

        if ($results) {
            foreach ($results[0] as $result) {
                if (!empty($result)) {
                    $title .= $result;
                }
            }
        }

        return $title;
    }

    public function getIdentify()
    {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        $random = $chars[mt_rand(0, 61)].$chars[mt_rand(0, 61)].$chars[mt_rand(0, 61)].$chars[mt_rand(0, 61)].$chars[mt_rand(0, 61)];
        $identify = substr(uniqid().$random, 0, 12);
        $this->getSettingService()->set('llpay_identify', $identify);

        return $identify;
    }

    private function getSettingService()
    {
        return ServiceKernel::instance()->createService('System.SettingService');
    }
}
