<?php

namespace Biz\Visualization\Dao\Impl;

use Biz\Visualization\Dao\CoursePlanStayDailyDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class CoursePlanStayDailyDaoImpl extends AdvancedDaoImpl implements CoursePlanStayDailyDao
{
    protected $table = 'course_plan_stay_daily';

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
