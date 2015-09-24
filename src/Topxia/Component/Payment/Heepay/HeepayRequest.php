<?php
namespace Topxia\Component\Payment\Heepay;

use Topxia\Component\Payment\Request;

class HeepayRequest extends Request {

    protected $url = 'https://pay.heepay.com/Payment/Index.aspx';

    public function form()
    {
        $form = array();
        $form['action'] = $this->url . '?_input_charset=utf-8';
        $form['method'] = 'post';
        $form['params'] = $this->convertParams($this->params);
        return $form;
    }

    public function signParams($params) {
        unset($params['goods_name'],$params['remark']);
        $sign = '';
        foreach ($params as $key => $value) {
            if (empty($value)) {
                continue;
            }
            $sign .= $key . '=' . $value . '&';
        }
        $sign = substr($sign, 0, - 1);
        $sign .='&'.$this->options['secret'];
        var_dump($sign);
        exit();
        return md5($sign);
    }

    protected function convertParams($params)
    {
        $converted = array();

        $converted['version'] = 1;
        $converted['agent_id'] = $this->options['key'];
        $converted['agent_bill_id']= $params['orderSn'];
        $converted['agent_bill_time']=date("YmdHis",time());
        $converted['pay_type'] = 20;
        $converted['pay_amt'] = $params['amount'];
        if (!empty($params['notifyUrl'])) {
            $converted['notify_url'] = $params['notifyUrl'];
        }
        if (!empty($params['returnUrl'])) {
            $converted['return_url'] = $params['returnUrl'];
        }
        $converted['user_ip']=str_replace(".", "_", $this->get_client_ip());
        $converted['is_test']=1;
        $converted['goods_name']=$this->filterText($params['title']);
        $converted['remark']='';
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

    private function get_client_ip()
    {
        if ($_SERVER['REMOTE_ADDR']) {
            $cip = $_SERVER['REMOTE_ADDR'];
        } elseif (getenv("REMOTE_ADDR")) {
            $cip = getenv("REMOTE_ADDR");
        } elseif (getenv("HTTP_CLIENT_IP")) {
            $cip = getenv("HTTP_CLIENT_IP");
        } else {
            $cip = "unknown";
        }
        return $cip;
    }

}