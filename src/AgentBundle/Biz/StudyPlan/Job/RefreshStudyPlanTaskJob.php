<?php

namespace AgentBundle\Biz\StudyPlan\Job;

use AgentBundle\Biz\StudyPlan\Service\StudyPlanService;
use AgentBundle\Workflow\TaskScheduler;
use AppBundle\Common\ArrayToolkit;
use Biz\Task\Service\TaskService;
use Codeages\Biz\Framework\Scheduler\AbstractJob;

class RefreshStudyPlanTaskJob extends AbstractJob
{
    public function execute()
    {
        $planTasks = $this->getStudyPlanService()->searchPlanTasks(['studyDate' => date('Y-m-d', strtotime('-1 day')), 'learned' => 0], [], 0, PHP_INT_MAX);
        if (empty($planTasks)) {
            return;
        }
        $plans = $this->getStudyPlanService()->findActivePlansByIds(array_column($planTasks, 'planId'));
        $planTasks = $this->getStudyPlanService()->searchPlanTasks(['planIds' => array_column($plans, 'id'), 'learned' => 0], ['studyDate' => 'ASC', 'id' => 'ASC'], 0, PHP_INT_MAX);
        $courseTasks = $this->getTaskService()->findTasksByIds(array_column($planTasks, 'taskId'));
        $courseTasks = array_column($courseTasks, null, 'id');
        $planTasksGroup = ArrayToolkit::group($planTasks, 'planId');
        $taskScheduler = new TaskScheduler();
        $plans = array_column($plans, null, 'id');
        foreach ($planTasksGroup as $planId => $tasks) {
            $plan = $plans[$planId];
            $plan['dailyLearnDuration'] = $plan['dailyAvgTime'] / 60;
            $this->getStudyPlanService()->generatePlanTasks($planId, $taskScheduler->schedule($plan, $this->makeTasks($tasks, $courseTasks)));
        }
    }

    private function makeTasks($planTasks, $courseTasks)
    {
        $newTasks = [];
        foreach (ArrayToolkit::group($planTasks, 'taskId') as $taskId => $planTasksGroup) {
            $courseTask = $courseTasks[$taskId];
            $courseTask['duration'] = array_sum(array_column($planTasksGroup, 'targetDuration')) - array_sum(array_column($planTasksGroup, 'learnedDuration'));
            $newTasks[] = $courseTask;
        }

        return $newTasks;
    }

    /**
     * @return TaskService
     */
    private function getTaskService()
    {
        return $this->biz->service('Task:TaskService');
    }

    /**
     * @return StudyPlanService
     */
    private function getStudyPlanService()
    {
        return $this->biz->service('AgentBundle:StudyPlan:StudyPlanService');
    }
}
