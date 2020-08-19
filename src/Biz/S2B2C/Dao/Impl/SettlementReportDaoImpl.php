<?php

namespace Biz\S2B2C\Dao\Impl;

use Biz\S2B2C\Dao\SettlementReportDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class SettlementReportDaoImpl extends AdvancedDaoImpl implements SettlementReportDao
{
    protected $table = 's2b2c_product_settlement_report';

    public function declares()
    {
        return [
            'serializes' => [],
            'orderbys' => [
                'createdTime',
                'updatedTime',
                'id',
            ],
            'timestamps' => ['createdTime', 'updatedTime'],
            'conditions' => [
                'id = :id',
                'supplierId = :supplierId',
                'productId = :productId',
                'type = :type',
                'userId = :userId',
                'nickname LIKE :nicknameLike',
                'orderId = :orderId',
                'status = :status'
            ],
        ];
    }
}