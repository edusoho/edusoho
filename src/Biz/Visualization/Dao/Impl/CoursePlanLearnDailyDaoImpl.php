<?php

namespace Biz\Visualization\Dao\Impl;

use Biz\Visualization\Dao\CoursePlanLearnDailyDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class CoursePlanLearnDailyDaoImpl extends AdvancedDaoImpl implements CoursePlanLearnDailyDao
{
    protected $table = 'course_plan_learn_daily';

    public function declares()
    {
        return [
            'timestamps' => ['createdTime', 'updatedTime'],
            'serializes' => [
            ],
            'conditions' => [
                'id = :id',
            ],
            'orderbys' => ['id', 'createdTime'],
        ];
    }
}
