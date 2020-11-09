<?php

namespace Biz\Visualization\Dao\Impl;

use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class ActivityVideoWatchRecordDaoImpl extends AdvancedDaoImpl
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
            ],
            'orderbys' => ['id', 'createdTime'],
        ];
    }
}
