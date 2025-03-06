<?php

namespace Biz\StudyPlan\Dao\Impl;

use Biz\StudyPlan\Dao\StudyPlanDetail;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class StudyPlanDetailImpl extends GeneralDaoImpl implements StudyPlanDetail
{
    protected $table = 'study_plan_detail';

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
