<?php
namespace Topxia\Component\Payment\Tenpay;

use Topxia\Component\Payment\Request;

class TenpayRequest extends Request {

    protected $paramMap = array(
        self::SIGN_TYPE => 'sign_type',
        self::VERSION => 'service_version',
        self::CHARSET => 'input_charset',
        self::SIGN => 'sign',
        self::SERVICE => 'trade_mode',
        self::TITLE => 'subject',
        self::SUMMARY => 'body',
        self::EXTRA_PARAM => 'attach',
        self::RETURN_URL => 'return_url',
        self::NOTIFY_URL => 'notify_url',
        self::PARTNER => 'partner',
        self::TRANSACTION_ID => 'out_trade_no',
        self::AMOUNT => 'total_fee',
        self::IP => 'spbill_create_ip',
        self::TRANSACTION_DATE => 'transactionDate',
        self::TIMEOUT => 'timeout',
        self::SELLER => 'seller_id',
    );

    protected $requestUrl = 'https://gw.tenpay.com/gateway/pay.htm';

    protected $defaultRequestParams = array(
        'fee_type' => '1',
        'input_charset' => 'utf-8',
    );

    public function setParam($key, $value) {
        if ($key == self::AMOUNT) {
            $value = $value *100;
        }
        parent::setParam($key, $value);
    }

    public function signParams($params) {
        ksort($params);
        $sign = '';
        foreach ($params as $key => $value) {
            if (($key == 'sign') || ($value == "") || ($value === null)) {
                continue;
            }
            $sign .= $key . '=' . $value . '&';
        }
        $sign .= 'key=' . $this->options['secret'];
        return strtolower(md5($sign));
    }

}