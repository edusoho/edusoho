<?php

namespace Biz\Coupon\Dao\Impl;

use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;
use Biz\Coupon\Dao\CouponBatchResourceDao;

class CouponBatchResourceDaoImpl extends AdvancedDaoImpl implements CouponBatchResourceDao
{
    protected $table = 'coupon_batch_resource';

    public function declares()
    {
        return array(
            'serializes' => array(),
            'orderbys' => array('createdTime', 'id'),
            'timestamps' => array('createdTime'),
            'conditions' => array(
                'batchId = :batchId',
                'targetId = :targetId',
                'targetId IN (:targetIds)',
                'targetType = :targetType',
                'targetType IN (:targetTypes)',
            ),
        );
    }
}
