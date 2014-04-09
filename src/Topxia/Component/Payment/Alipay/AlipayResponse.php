<?php
namespace Topxia\Component\Payment\Alipay;

use Topxia\Component\Payment\Response;

class AlipayResponse extends Response
{

    public function getPayData()
    {
        $error = $this->hasError();
        if ($error) {
            throw new \RuntimeException(sprintf('支付宝支付校验失败(%s)。', $error));
        }

        $params = $this->params;

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

        if (!empty($this->params['notify_id'])) {
            $notifyResult = $this->getRequest('https://mapi.alipay.com/gateway.do', array(
                'notify_id' => $this->params['notify_id'],
                'service' => 'notify_verify',
                'partner' => $this->options['key'],
            ));

            // if (strtolower($notifyResult) !== 'true') {
            //     return 'notify_verify_error';
            // }
        }

        return false;
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