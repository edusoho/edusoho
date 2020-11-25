<?php

namespace Biz\Visualization\Dao\Impl;

use Biz\Visualization\Dao\ActivityVideoDailyDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class ActivityVideoDailyDaoImpl extends AdvancedDaoImpl implements ActivityVideoDailyDao
{
    protected $table = 'activity_video_daily';

    public function declares()
    {
        return [
            'timestamps' => ['createdTime', 'updatedTime'],
            'serializes' => [
            ],
            'conditions' => [
                'id = :id',
                'dayTime = :dayTime',
                'taskId in (:taskIds)',
                'userId = :userId',
            ],
            'orderbys' => ['id', 'createdTime'],
        ];
    }
}
