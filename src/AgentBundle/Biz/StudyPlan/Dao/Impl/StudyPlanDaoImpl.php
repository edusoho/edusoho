<?php

namespace AgentBundle\Biz\StudyPlan\Dao\Impl;

use AgentBundle\Biz\StudyPlan\Dao\StudyPlanDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class StudyPlanDaoImpl extends GeneralDaoImpl implements StudyPlanDao
{
    protected $table = 'study_plan';

    public function getStudyPlanByUserIdAndCourseId($userId, $courseId)
    {
        return $this->getByFields(['userId' => $userId, 'courseId' => $courseId]);
    }

    public function declares()
    {
        return [
            'timestamps' => ['createdTime', 'updatedTime'],
            'orderbys' => ['id', 'createdTime', 'updatedTime'],
            'serializes' => ['weekDays' => 'json'],
            'conditions' => [
            ],
        ];
    }
}
