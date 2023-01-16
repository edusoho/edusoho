<?php

namespace Biz\Visualization\Dao\Impl;

use Biz\Visualization\Dao\ActivityVideoWatchRecordDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class ActivityVideoWatchRecordDaoImpl extends AdvancedDaoImpl implements ActivityVideoWatchRecordDao
{
    protected $table = 'activity_video_watch_record';

    public function declares()
    {
        return [
            'timestamps' => ['createdTime'],
            'serializes' => [
                'data' => 'json',
            ],
            'conditions' => [
                'id = :id',
                'startTime >= :startTime_GE',
                'endTime < :endTime_LT',
                'userId = :userId',
                'taskId = :taskId',
                'courseId = :courseId',
                'activityId = :activityId',
            ],
            'orderbys' => ['id', 'createdTime'],
        ];
    }
}
