<?php

namespace Biz\S2B2C\Dao\Impl;

use Biz\S2B2C\Dao\ProductReportDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class ProductReportDaoImpl extends AdvancedDaoImpl implements ProductReportDao
{
    protected $table = 's2b2c_product_report';

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