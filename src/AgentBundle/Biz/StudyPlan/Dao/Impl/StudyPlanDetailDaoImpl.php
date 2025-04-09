<?php

namespace AgentBundle\Biz\StudyPlan\Dao\Impl;

use AgentBundle\Biz\StudyPlan\Dao\StudyPlanDetailDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class StudyPlanDetailDaoImpl extends AdvancedDaoImpl implements StudyPlanDetailDao
{
    protected $table = 'study_plan_detail';

    public function getByPlanIdAndStudyDate($planId, $studyDate)
    {
        return $this->getByFields(['planId' => $planId, 'studyDate' => $studyDate]);
    }

    public function declares()
    {
        return [
            'timestamps' => ['createdTime', 'updatedTime'],
            'orderbys' => ['id', 'studyDate', 'createdTime', 'updatedTime'],
            'serializes' => ['tasks' => 'json'],
            'conditions' => [
                'id IN (:ids)',
                'planId = :planId',
                'studyDate = :studyDate',
                'learned = :learned',
                'courseId IN (:courseIds)',
                'studyDate > :studyDate_GT',
            ],
        ];
    }
}
