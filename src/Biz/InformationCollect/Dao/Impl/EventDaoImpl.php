<?php

namespace Biz\InformationCollect\Dao\Impl;

use Biz\InformationCollect\Dao\EventDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class EventDaoImpl extends GeneralDaoImpl implements EventDao
{
    protected $table = 'information_collect_event';

    public function declares()
    {
        return [
            'serializes' => [
            ],
            'orderbys' => [
                'id', 'createdTime',
            ],
            'timestamps' => [
                'createdTime',
                'updatedTime',
            ],
            'conditions' => [
                'id = :id',
                'title like :title',
                'createdTime >= :startDate',
                'createdTime < :endDate',
            ],
        ];
    }
}
