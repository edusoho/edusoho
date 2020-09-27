<?php

namespace Biz\InformationCollect\Dao\Impl;

use Biz\InformationCollect\Dao\LocationDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class LocationDaoImpl extends AdvancedDaoImpl implements LocationDao
{
    protected $table = 'information_collect_location';

    public function declares()
    {
        return [
            'serializes' => [],
            'orderbys' => [
                'id',
            ],
            'timestamps' => [
                'createdTime',
            ],
            'conditions' => [
                'id = :id',
                'id IN (:ids)',
                'eventId = :eventId',
                'targetType IN (:targetTypes)',
                'targetType = :targetType',
                'targetId IN (:targetIds)',
                'eventId <> :excludeEventId',
                'eventId = :eventId',
                'action = :action',
                'targetId <> :excludeTargetId',
                'targetId <= :targetId_LTE',
            ],
        ];
    }
}
