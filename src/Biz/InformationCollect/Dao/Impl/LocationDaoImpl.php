<?php

namespace Biz\InformationCollect\Dao\Impl;

use Biz\InformationCollect\Dao\LocationDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class LocationDaoImpl extends AdvancedDaoImpl implements LocationDao
{
    protected $table = 'information_collect_location';

    public function findByEventIds($eventIds)
    {
        return $this->findInField('eventId', $eventIds);
    }

    public function declares()
    {
        return [
            'serializes' => [
                'targetId' => 'delimiter',
            ],
            'orderbys' => [
                'id',
            ],
            'timestamps' => [
                'createdTime',
            ],
            'conditions' => [
                'id = :id',
                'eventId IN (:eventIds)'
            ],
        ];
    }
}
