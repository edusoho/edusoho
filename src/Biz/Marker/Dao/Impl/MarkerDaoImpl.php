<?php

namespace Biz\Marker\Dao\Impl;

use Biz\Marker\Dao\MarkerDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class MarkerDaoImpl extends GeneralDaoImpl implements MarkerDao
{
    protected $table = 'marker';

    public function findByIds($ids)
    {
        return $this->findInField('id', $ids);
    }

    public function findByActivityId($activityId)
    {
        return $this->findByFields(['activityId' => $activityId]);
    }

    public function declares()
    {
        return [
            'timestamps' => ['createdTime', 'updatedTime'],
            'orderbys' => ['createdTime'],
            'conditions' => [
                'mediaId = :mediaId',
                'activityId = :activityId',
                'second = :second',
            ],
        ];
    }
}
