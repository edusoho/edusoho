<?php

namespace Codeages\Biz\Pay\Payment;

use Codeages\Biz\Framework\Service\Exception\AccessDeniedException;
use Codeages\Biz\Framework\Service\Exception\InvalidArgumentException;
use Codeages\Biz\Framework\Util\ArrayToolkit;

class LianlianPayGateway extends AbstractGateway
{
//    protected $url = 'https://yintong.com.cn/payment/bankgateway.htm';
//    protected $wapUrl = 'https://yintong.com.cn/llpayh5/payment.htm';

    protected $url = 'https://cashier.lianlianpay.com/payment/bankgateway.htm';
    protected $wapUrl = 'https://wap.lianlianpay.com/payment.htm';

    protected $isWap = false;

    public function createTrade($data)
    {
        if (!ArrayToolkit::requireds($data, array(
            'goods_title',
            'goods_detail',
            'attach',
            'trade_sn',
            'amount',
            'notify_url',
            'return_url',
            'create_ip',
        ))) {
            throw new InvalidArgumentException('trade args is invalid.');
        }

        $platformType = empty($data['platform_type']) ? 'Web' : $data['platform_type'];

        if ($platformType == 'Wap') {
            $this->url = $this->wapUrl;
            $this->isWap = true;
        }

        $data = $this->convertParams($data);
        return array(
            'url' => $this->url.'?'.http_build_query($data),
            'data' => $data
        );
    }

    public function converterNotify($data)
    {
        $data = ArrayToolkit::parts($data, array(
            'oid_partner',
            'sign_type',
            'sign',
            'dt_order',
            'no_order',
            'oid_paybill',
            'money_order',
            'result_pay',
            'settle_date',   //此属性出账日期，用于对账用，对账时才能返回付款时间或退款时间
            'info_order',
            'pay_type',
            'bank_code'
        ));

        $setting = $this->getSetting();
        if (!$setting['signatureToolkit']->signVerify($data, array('accessKey'=>$setting['accessKey']))) {
            return array(
                array(
                    'status' => 'failture',
                    'notify_data' => $data,
                ),
                'fail'
            );
        }

        return array(array(
                'status' => 'paid',
                'cash_flow' => $data['oid_paybill'],
                'paid_time' => time(),
                'pay_amount' => (int)($data['money_order']*100),
                'cash_type' => 'CNY',
                'trade_sn' => $data['no_order'],
                'attach' => array(),
                'notify_data' => $data,
            ),
            json_encode(array(
                'ret_code' => '0000',
                'ret_msg' => '交易成功'
            ))
        );
    }

    public function applyRefund($data)
    {
        throw new AccessDeniedException('can not apply refund with lianlianpay.');
    }

    public function queryTrade($tradeSn)
    {
        return null;
    }

    public function converterRefundNotify($data)
    {
        throw new AccessDeniedException('can not convert refund notify with lianlianpay.');
    }

    protected function signParams($params, $options)
    {
        $setting = $this->getSetting();
        return $setting['signatureToolkit']->signParams($params, $options);
    }

    protected function convertParams($params)
    {
        $setting = $this->getSetting();
        $converted                 = array();
        $converted['busi_partner'] = '101001';
        $converted['dt_order']     = date('YmdHis', time());
        $converted['money_order']  = $params['amount']/100;
        $converted['name_goods']   = mb_substr($this->filterText($params['goods_title']), 0, 12, 'utf-8');
        $converted['no_order']     = $params['trade_sn'];
        if (!empty($params['notify_url'])) {
            $converted['notify_url'] = $params['notify_url'];
        }
        $converted['sign_type']    = 'RSA';
        $converted['version']      = '1.0';

        $converted['oid_partner']  = $setting['oid_partner'];
        $converted['user_id']      = $params['attach']['identify_user_id'];

        $converted['timestamp']    = date('YmdHis', time());
        if (!empty($params['return_url'])) {
            $converted['url_return'] = $params['return_url'];
        }
        if (empty($params['attach']['bindPhone'])) {
            $params['attach']['bindPhone'] = '';
        }
        $converted['risk_item']  = json_encode(array(
            'frms_ware_category'=>1008,
            'user_info_mercht_userno'=>$params['attach']['identify_user_id'],
            'user_info_dt_register'=>date('YmdHis', $params['attach']['user_created_time']),
            'user_info_bind_phone' => $params['attach']['bindPhone']
        ));

        $converted['userreq_ip'] = str_replace(".", "_", $params['create_ip']);
        $converted['bank_code']  = '';
        $converted['pay_type']   = '2';
        $converted['sign']       = $this->signParams($converted, $setting);

        if ($this->isWap) {
            $converted['back_url'] = $params['show_url'];
            return $this->convertMobileParams($converted);
        } else {
            return $converted;
        }
    }

    protected function convertMobileParams($converted)
    {
        unset($converted['userreq_ip'], $converted['bank_code'], $converted['pay_type'], $converted['timestamp'], $converted['version'], $converted['sign']);
        $converted['version'] = '1.2';
        $converted['app_request'] = 3;
        $converted['sign'] = $this->signParams($converted, $this->getSetting());
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

    protected function getSetting()
    {
        $config = $this->biz['payment.platforms']['lianlianpay'];
        return array(
            'secret' => $config['secret'],
            'accessKey' => $config['accessKey'],
            'oid_partner' => $config['oid_partner'],
            'signatureToolkit' => $config['signatureToolkit'],
        );
    }
}
