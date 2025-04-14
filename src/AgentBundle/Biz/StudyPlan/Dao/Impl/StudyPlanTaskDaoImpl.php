<?php

namespace AgentBundle\Biz\StudyPlan\Dao\Impl;

use AgentBundle\Biz\StudyPlan\Dao\StudyPlanTaskDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class StudyPlanTaskDaoImpl extends AdvancedDaoImpl implements StudyPlanTaskDao
{
    protected $table = 'study_plan_task';

    public function declares()
    {
        return [
            'timestamps' => ['createdTime', 'updatedTime'],
            'orderbys' => ['id', 'studyDate', 'createdTime', 'updatedTime'],
            'conditions' => [
                'id IN (:ids)',
                'planId = :planId',
                'planId IN (:planIdIds)',
                'studyDate = :studyDate',
                'learned = :learned',
                'courseId IN (:courseIds)',
                'courseId = :courseId',
                'taskId = :taskId',
                'taskId != :excludeTaskId',
            ],
        ];
    }
}
