<?php

namespace Biz\GoodsMarketing\Dao\Impl;

use Biz\GoodsMarketing\Dao\MarketingMeansDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class MarketingMeansDaoImpl extends AdvancedDaoImpl implements MarketingMeansDao
{
    protected $table = 'marketing_means';

    public function declares()
    {
        return [
            'conditions' => [
                'id = :id',
                'type = :type',
                'targetId = :targetId',
                'targetType = :targetType',
                'fromMeansId = :fromMeansId',
            ],
            'serializes' => [
            ],
            'orderbys' => ['id', 'createdTime', 'updatedTime'],
            'timestamps' => ['createdTime', 'updatedTime'],
        ];
    }

    public function findValidMeansByTargetTypeAndTargetId($targetType, $targetId)
    {
        return $this->findByFields([
            'targetType' => $targetType,
            'targetId' => $targetId,
            'status' => 1,
            'visibleOnGoodsPage' => 1,
        ]);
    }
}
