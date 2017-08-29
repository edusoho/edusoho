<?php

namespace Codeages\Biz\Framework\Pay\Dao\Impl;

use Codeages\Biz\Framework\Pay\Dao\PaymentTradeDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class PaymentTradeDaoImpl extends GeneralDaoImpl implements PaymentTradeDao
{
    protected $table = 'biz_payment_trade';

    public function getByOrderSnAndPlatform($orderSn, $platform)
    {
        return $this->getByFields(array(
            'order_sn' => $orderSn,
            'platform' => $platform
        ));
    }

    public function findByOrderSn($orderSn)
    {
        return $this->findByFields(array(
            'order_sn' => $orderSn,
        ));
    }

    public function getByTradeSn($sn)
    {
        return $this->getByFields(array(
            'trade_sn' => $sn,
        ));
    }

    public function declares()
    {
        return array(
            'timestamps' => array('created_time', 'updated_time'),
            'serializes' => array(
                'platform_created_result' => 'json',
                'notify_data' => 'json'
            )
        );
    }
}