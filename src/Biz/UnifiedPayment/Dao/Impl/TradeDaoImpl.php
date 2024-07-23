<?php

namespace Biz\UnifiedPayment\Dao\Impl;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use Codeages\Biz\Pay\Dao\PayTradeDao;

class TradeDaoImpl extends GeneralDaoImpl implements PayTradeDao
{
    protected $table = 'unified_payment_trade';

    public function getById($id)
    {
        return $this->getByFields([
            'id' => $id,
        ]);
    }

    public function getByOrderSnAndPlatform($orderSn, $platform)
    {
        return $this->getByFields([
            'orderSn' => $orderSn,
            'platform' => $platform,
        ]);
    }

    public function findByIds($ids)
    {
        return $this->findInField('id', $ids);
    }

    public function findByOrderSns($orderSns)
    {
        return $this->findInField('orderSn', $orderSns);
    }

    public function findByOrderSn($orderSn)
    {
        return $this->findByFields([
            'orderSn' => $orderSn,
        ]);
    }

    public function getByTradeSn($sn)
    {
        return $this->getByFields([
            'tradeSn' => $sn,
        ]);
    }

    public function findByTradeSns($sns)
    {
        return $this->findInField('tradeSn', $sns);
    }

    public function getByPlatformSn($platformSn)
    {
        return $this->getByFields([
            'platformSn' => $platformSn,
        ]);
    }

    public function declares()
    {
        return [
            'timestamps' => ['createdTime', 'updatedTime'],
            'serializes' => [
                'platformCreatedResult' => 'json',
                'notifyData' => 'json',
                'platformCreatedParams' => 'json',
            ],
            'orderbys' => [
                'createdTime',
            ],
            'conditions' => [
                'id IN (:ids)',
                'orderSn IN (:orderSns)',
                'orderSn NOT IN (:exceptOrderSns)',
                'title LIKE :likeTitle',
                'status = :status',
                'status IN (:statuses)',
                'type IN (:types)',
                'cashAmount > :cashAmount_GE',
                'userId = :userId',
                'invoiceSn = :invoiceSn',
                'invoiceSn IN (:invoiceSns)',
                'tradeSn IN (:tradeSns)',
                'createdTime >= :createdTime_GTE',
                'createdTime <= :createdTime_LTE',
            ],
        ];
    }
}
