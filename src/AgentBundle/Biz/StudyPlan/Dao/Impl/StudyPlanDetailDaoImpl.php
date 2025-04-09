<?php

namespace AgentBundle\Biz\StudyPlan\Dao\Impl;

use AgentBundle\Biz\StudyPlan\Dao\StudyPlanDetailDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class StudyPlanDetailDaoImpl extends AdvancedDaoImpl implements StudyPlanDetailDao
{
    protected $table = 'study_plan_detail';

    public function declares()
    {
        return [
            'timestamps' => ['createdTime', 'updatedTime'],
            'orderbys' => ['id', 'createdTime', 'updatedTime'],
            'serializes' => ['taskIds' => 'json'],
            'conditions' => [
                'studyDate = :studyDate',
                'learned = :learned',
                'courseId IN (:courseIds)',
            ],
        ];
    }
}
