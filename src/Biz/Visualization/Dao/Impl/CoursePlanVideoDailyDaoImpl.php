<?php

namespace Biz\Visualization\Dao\Impl;

use Biz\Visualization\Dao\CoursePlanVideoDailyDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class CoursePlanVideoDailyDaoImpl extends AdvancedDaoImpl implements CoursePlanVideoDailyDao
{
    protected $table = 'course_plan_video_daily';

    public function declares()
    {
        return [
            'timestamps' => ['createdTime', 'updatedTime'],
            'serializes' => [
            ],
            'conditions' => [
                'id = :id',
                'dayTime = :dayTime',
            ],
            'orderbys' => ['id', 'createdTime'],
        ];
    }
}
