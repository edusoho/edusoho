<?php

namespace Topxia\MobileBundleV2\Alipay;

use AppBundle\Component\Payment\Response;

class MobileAlipayResponse extends Response
{
    public function getPayData()
    {
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
}
