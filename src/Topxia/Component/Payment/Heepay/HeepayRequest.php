<?php
namespace Topxia\Component\Payment\Heepay;

use Topxia\Component\Payment\Request;

class HeepayRequest extends Request
{
    protected $url = 'https://pay.heepay.com/Payment/Index.aspx';

    public function form()
    {
        $form           = array();
        $form['action'] = $this->url;
        $form['method'] = 'post';
        $form['params'] = $this->convertParams($this->params);
        return $form;
    }

    public function signParams($params)
    {
        unset($params['pay_code'], $params['goods_name'], $params['remark']);
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
        $converted                    = array();
        $converted['version']         = 1;
        $converted['agent_id']        = $this->options['key'];
        $converted['agent_bill_id']   = strtolower($this->generateOrderToken());
        $converted['agent_bill_time'] = date("YmdHis", time());
        $converted['pay_type']        = '20';
        $converted['pay_code']        = '0';
        $converted['pay_amt']         = $params['amount'];

        if (!empty($params['notifyUrl'])) {
            $converted['notify_url'] = $params['notifyUrl'];
        }

        if (!empty($params['returnUrl'])) {
            $converted['return_url'] = $params['returnUrl'];
        }

        $converted['user_ip']    = str_replace(".", "_", $this->getClientIp());
        $converted['goods_name'] = urlencode(mb_substr($this->filterText($params['title']), 0, 50, 'utf-8'));
        $converted['remark']     = '';
        $converted['sign']       = $this->signParams($converted);
        return $converted;
    }

    protected function filterText($text)
    {
        return str_replace(array('#', '%', '&', '+'), array('＃', '％', '＆', '＋'), $text);
    }

    private function generateOrderToken()
    {
        return 'h'.date('YmdHis', time()).mt_rand(10000, 99999);
    }
}
