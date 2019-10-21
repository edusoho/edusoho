<?php

namespace Codeages\Biz\Pay\Dao\Impl;

use Codeages\Biz\Framework\Context\Biz;
use Codeages\Biz\Pay\Dao\PayTradeDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class PayTradeDaoImpl extends GeneralDaoImpl implements PayTradeDao
{
    protected $table = 'biz_pay_trade';

    public function getById($id)
    {
        return $this->getByFields(array(
            'id' => $id
        ));
    }

    public function getByOrderSnAndPlatform($orderSn, $platform)
    {
        return $this->getByFields(array(
            'order_sn' => $orderSn,
            'platform' => $platform
        ));
    }

    public function findByIds($ids)
    {
        return $this->findInField('id', $ids);
    }

    public function findByOrderSns($orderSns)
    {
        return $this->findInField('order_sn', $orderSns);
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

    public function findByTradeSns($sns)
    {
        return $this->findInField('trade_sn', $sns);
    }

    public function getByPlatformSn($platformSn)
    {
        return $this->getByFields(array(
            'platform_sn' => $platformSn,
        ));
    }

    public function declares()
    {
        return array(
            'timestamps' => array('created_time', 'updated_time'),
            'serializes' => array(
                'platform_created_result' => 'json',
                'notify_data' => 'json',
                'platform_created_params' => 'json'
            ),
            'orderbys' => array(
                'created_time',
            ),
            'conditions' => array(
                'id IN (:ids)',
                'order_sn IN (:order_sns)',
                'order_sn NOT IN (:except_order_sns)',
                'title LIKE :like_title',
                'status = :status',
                'type IN (:types)',
                'cash_amount > :cash_amount_GE',
                'user_id = :user_id',
                'invoice_sn = :invoice_sn',
                'invoice_sn IN (:invoice_sns)',
                'created_time >= :created_time_GTE',
                'created_time <= :created_time_LTE',
            ),
        );
    }
}
