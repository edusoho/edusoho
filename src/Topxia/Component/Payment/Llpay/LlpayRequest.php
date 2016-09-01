<?php
namespace Topxia\Component\Payment\Llpay;

use Topxia\Component\Payment\Request;
use Topxia\Service\Common\ServiceKernel;

class LlpayRequest extends Request
{
    protected $url = 'https://yintong.com.cn/payment/bankgateway.htm';

    public function form()
    {
        $form           = array();
        if ($this->params['isMobile']) {
            $this->url = 'https://yintong.com.cn/llpayh5/payment.htm';
        }
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
        $converted                 = array();
        $converted['busi_partner'] = '101001';
        $converted['dt_order']     = date('YmdHis', time());
        $converted['money_order']  = $params['amount'];
        $converted['name_goods']   = mb_substr($this->filterText($params['title']), 0, 12, 'utf-8');
        $converted['no_order']     = $params['orderSn'];
        if (!empty($params['notifyUrl'])) {
            $converted['notify_url'] = $params['notifyUrl'];
        }
        $converted['oid_partner']  = $this->options['key'];
        $converted['sign_type']    = 'MD5';
        $converted['version']      = '1.0';
        $identify = $this->getSettingService()->get('llpay_identify');
        if (!$identify) {
            $identify = $this->getIdentify();
        }
        $converted['user_id']      = $identify."_".$params['userId'];
        $converted['timestamp']    = date('YmdHis', time());
        if (!empty($params['returnUrl'])) {
            $converted['url_return'] = $params['returnUrl'];
        }

        $converted['userreq_ip'] = str_replace(".", "_", $this->getClientIp());
        $converted['bank_code']  = '';
        $converted['pay_type']   = '2';
        $converted['risk_item']  = json_encode(array('frms_ware_category'=>3001,'user_info_mercht_userno'=>$identify."_".$params['userId']));
        if ($params['isMobile']) {
            $converted['back_url'] = $params['backUrl'];
        }
        $converted['sign']       = $this->signParams($converted);
        if ($params['isMobile']) {
            return $this->convertMobileParams($converted, $params['userAgent']);
        } else {
            return $converted;
        }
    }

    public function convertMobileParams($converted, $userAgent)
    {
        unset($converted['userreq_ip'], $converted['bank_code'], $converted['pay_type'], $converted['timestamp'], $converted['version'], $converted['sign']);
        $converted['version'] = '1.2';
        $converted['app_request'] = 3;
        $converted['sign'] = $this->signParams($converted);
        return array('req_data'=>json_encode($converted));
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
        $identify = substr(md5(uniqid()), 0, 12);
        $this->getSettingService()->set('llpay_identify', $identify);
        return $identify;
    }

    private function getSettingService()
    {
        return ServiceKernel::instance()->createService('System.SettingService');
    }
}
