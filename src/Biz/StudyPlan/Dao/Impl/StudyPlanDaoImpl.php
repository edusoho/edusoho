<?php

namespace Biz\StudyPlan\Dao\Impl;

use Biz\StudyPlan\Dao\StudyPlanDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class StudyPlanDaoImpl extends GeneralDaoImpl implements StudyPlanDao
{
    protected $table = 'study_plan';

    public function declares()
    {
        return [
            'timestamps' => ['createdTime', 'updatedTime'],
            'orderbys' => ['id', 'createdTime', 'updatedTime'],
            'conditions' => [
            ],
        ];
    }
}
