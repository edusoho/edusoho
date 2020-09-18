<?php

namespace Biz\InformationCollect\Dao\Impl;

use Biz\InformationCollect\Dao\EventDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class EventDaoImpl extends GeneralDaoImpl implements EventDao
{
    public function declares()
    {
        return [
            'serializes' => [
            ],
            'orderbys' => [
                'id',
            ],
            'timestamps' => [
                'createdTime',
                'updatedTime',
            ],
            'conditions' => [
                'id = :id',
            ],
        ];
    }
}
