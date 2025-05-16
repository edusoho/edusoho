<?php

namespace AgentBundle\Biz\StudyPlan\Service\Impl;

use AgentBundle\Biz\StudyPlan\Dao\StudyPlanDao;
use AgentBundle\Biz\StudyPlan\Dao\StudyPlanTaskDao;
use AgentBundle\Biz\StudyPlan\Service\StudyPlanService;
use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use Biz\Common\CommonException;

class StudyPlanServiceImpl extends BaseService implements StudyPlanService
{
    public function generatePlan($data)
    {
        if (!ArrayToolkit::requireds($data, ['courseId', 'startDate', 'endDate', 'weekDays', 'dailyAvgTime'])) {
            throw CommonException::ERROR_PARAMETER_MISSING();
        }
        $studyPlan = $this->getStudyPlanDao()->getStudyPlanByUserIdAndCourseId($this->getCurrentUser()->getId(), $data['courseId']);
        if (empty($studyPlan)) {
            return $this->getStudyPlanDao()->create([
                'userId' => $this->getCurrentUser()->getId(),
                'courseId' => $data['courseId'],
                'startDate' => $data['startDate'],
                'endDate' => $data['endDate'],
                'weekDays' => $data['weekDays'],
                'totalDays' => 0,
                'dailyAvgTime' => $data['dailyAvgTime'],
            ]);
        }
        $data = ArrayToolkit::parts($data, ['startDate', 'endDate', 'weekDays', 'dailyAvgTime']);

        return $this->getStudyPlanDao()->update($studyPlan['id'], $data);
    }

    public function deletePlan($id)
    {
        $this->getStudyPlanDao()->delete($id);
    }

    public function generatePlanTasks($planId, $tasks)
    {
        $this->getStudyPlanTaskDao()->batchDelete(['planId' => $planId, 'learned' => 0]);
        $plan = $this->getStudyPlanDao()->get($planId);
        $planTasks = [];
        foreach ($tasks as $task) {
            $planTasks[] = [
                'planId' => $planId,
                'courseId' => $plan['courseId'],
                'studyDate' => $task['date'],
                'taskId' => $task['id'],
                'targetDuration' => $task['duration'],
            ];
        }
        $this->getStudyPlanTaskDao()->batchCreate($planTasks);
    }

    public function deletePlanTasksByPlanId($planId)
    {
        $this->getStudyPlanTaskDao()->batchDelete(['planId' => $planId]);
    }

    public function searchPlanTasks($conditions, $orderBys, $start, $limit, $columns = [])
    {
        return $this->getStudyPlanTaskDao()->search($conditions, $orderBys, $start, $limit, $columns);
    }

    public function findActivePlansByIds($ids)
    {
        $plans = $this->getStudyPlanDao()->findByIds($ids);
        $plans = array_filter($plans, function ($plan) {
            return empty($plan['endDate']) || ($plan['endDate'] >= date('Y-m-d'));
        });

        return $plans;
    }

    public function findActivePlansByCourseId($courseId)
    {
        $plans = $this->getStudyPlanDao()->findByCourseId($courseId);
        $plans = array_filter($plans, function ($plan) {
            return empty($plan['endDate']) || ($plan['endDate'] >= date('Y-m-d'));
        });

        return $plans;
    }

    public function getPlanByCourseIdAndUserId($courseId, $userId)
    {
        return $this->getStudyPlanDao()->getStudyPlanByUserIdAndCourseId($userId, $courseId);
    }

    public function isUserStudyPlanGenerated($userId, $courseId)
    {
        $studyPlan = $this->getPlanByCourseIdAndUserId($courseId, $userId);

        return !empty($studyPlan);
    }

    public function wavePlanTaskLearnedDuration($id, $increment)
    {
        $this->getStudyPlanTaskDao()->wave([$id], ['learnedDuration' => $increment]);
    }

    public function markPlanTaskLearned($ids)
    {
        if (empty($ids)) {
            return;
        }
        $this->getStudyPlanTaskDao()->update(['ids' => $ids], ['learned' => 1]);
    }

    /**
     * @return StudyPlanDao
     */
    protected function getStudyPlanDao()
    {
        return $this->createDao('AgentBundle:StudyPlan:StudyPlanDao');
    }

    /**
     * @return StudyPlanTaskDao
     */
    protected function getStudyPlanTaskDao()
    {
        return $this->createDao('AgentBundle:StudyPlan:StudyPlanTaskDao');
    }
}
