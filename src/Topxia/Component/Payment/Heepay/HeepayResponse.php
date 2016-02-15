<?php
namespace Topxia\Component\Payment\Heepay;

use Topxia\Component\Payment\Response;
use Topxia\Service\Common\ServiceKernel;

class HeepayResponse extends Response
{
    protected $url = 'https://query.heepay.com/Payment/Query.aspx';

    public function getPayData()
    {
        $params = $this->params;
        $error  = $this->hasError($params);

        if ($error) {
            throw new \RuntimeException(sprintf('网银支付校验失败(%s)。', $error));
        }

        $data['payment'] = 'heepay';
        $data['sn']      = $this->getOrderSn($params['agent_bill_id']);

        $order = $this->getOrderService()->getOrderBySn($data['sn']);

        if ($order['status'] == 'paid') {
            $data['status'] = 'success';
        } else {
            $result      = $this->confirmSellerSendGoods();
            $returnArray = $this->toArray($result);

            if ($returnArray['result'] != 1) {
                throw new \RuntimeException('网银支付失败');
            }

            if (in_array($returnArray['result'], array(1))) {
                $data['status'] = 'success';
            } else {
                $data['status'] = 'unknown';
            }
        }

        $data['amount']   = $params['pay_amt'];
        $data['paidTime'] = time();

        $data['raw'] = $params;

        return $data;
    }

    private function hasError($params)
    {
        if ($params['result'] != 1 && !empty($params['pay_message'])) {
            return $params['pay_message'];
        }

        return false;
    }

    private function confirmSellerSendGoods()
    {
        $params                  = $this->params;
        $data                    = array();
        $data['version']         = 1;
        $data['agent_id']        = $params['agent_id'];
        $data['agent_bill_id']   = $params['agent_bill_id'];
        $data['agent_bill_time'] = date("YmdHis", time());
        $data['remark']          = $params['remark'];
        $data['return_mode']     = 1;
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

    public function getOrderSn($token)
    {
        if (stripos($token, 'c') !== false) {
            $order = $this->getOrderService()->getOrderByToken($token);
        }

        if (stripos($token, 'o') !== false) {
            $order = $this->getCashOrdersService()->getOrderByToken($token);
        }

        return $order['sn'];
    }

    private function signParams($params)
    {
        unset($params['sign'], $params['pay_message'], $params['remark']);
        $params = array_filter($params);
        $sign   = '';

        foreach ($params as $key => $value) {
            $sign .= $key.'='.strtolower($value).'&';
        }

        $sign .= 'key='.$this->options['secret'];
        return md5($sign);
    }

    private function toArray($result)
    {
        $data = explode("|", $result);

        if (count($data) <= 1) {
            throw new \RuntimeException(sprintf('该笔单据查询超过１次,请过15分钟之后查询'));
        }

        $param = array();

        if (is_array($data)) {
            foreach ($data as $value) {
                $arr            = explode("=", $value);
                $param[$arr[0]] = $arr[1];
            }
        }

        return $param;
    }

    protected function getServiceKernel()
    {
        return ServiceKernel::instance();
    }

    protected function getOrderService()
    {
        return $this->getServiceKernel()->createService('Order.OrderService');
    }

    protected function getCashOrdersService()
    {
        return $this->getServiceKernel()->createService('Cash.CashOrdersService');
    }
}
