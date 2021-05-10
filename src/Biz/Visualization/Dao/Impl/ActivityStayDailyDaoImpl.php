<?php

namespace Biz\Visualization\Dao\Impl;

use Biz\Visualization\Dao\ActivityStayDailyDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class ActivityStayDailyDaoImpl extends AdvancedDaoImpl implements ActivityStayDailyDao
{
    protected $table = 'activity_stay_daily';

    public function declares()
    {
        return [
            'timestamps' => ['createdTime', 'updatedTime'],
            'serializes' => [
            ],
            'conditions' => [
                'id = :id',
                'taskId in (:taskIds)',
                'dayTime = :dayTime',
                'userId = :userId',
            ],
            'orderbys' => ['id', 'createdTime'],
        ];
    }
}
