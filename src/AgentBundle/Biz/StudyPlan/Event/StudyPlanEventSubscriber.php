<?php

namespace AgentBundle\Biz\StudyPlan\Event;

use AgentBundle\Biz\StudyPlan\Service\StudyPlanService;
use Biz\Task\Service\TaskResultService;
use Codeages\Biz\Framework\Event\Event;
use Codeages\Biz\Framework\Event\EventSubscriber;

class StudyPlanEventSubscriber extends EventSubscriber
{
    public static function getSubscribedEvents()
    {
        return [
            'activity.doing' => 'onActivityDoing',
            'course.task.finish' => 'onCourseTaskFinish',
        ];
    }

    public function onActivityDoing(Event $event)
    {
        $task = $event->getArgument('task');
        $biz = $this->getBiz();
        $plan = $this->getStudyPlanService()->getPlanByCourseIdAndUserId($task['courseId'], $biz['user']['id']);
        $detail = $this->getStudyPlanService()->getPlanDetailByPlanIdAndStudyDate($plan['id'], date('Y-m-d'));
        if (!empty($detail['learned'])) {
            return;
        }
        if (empty($detail['tasks'][$task['id']])) {
            return;
        }
        $activity = $event->getSubject();
        if (in_array($activity['finishType'], ['end', 'watchTime'])) {
            $watchTime = $event->hasArgument('watchTime') ? $event->getArgument('watchTime') : 0;
            $detail['tasks'][$task['id']] = max($detail['tasks'][$task['id']] - $watchTime, 0);
        } else {
            $duration = $event->hasArgument('duration') ? $event->getArgument('duration') : 0;
            $detail['tasks'][$task['id']] = max($detail['tasks'][$task['id']] - $duration, 0);
        }
        $this->getStudyPlanService()->updatePlanDetailTasks($detail['id'], $detail['tasks']);
        if (array_sum(array_values($detail['tasks'])) == 0) {
            $this->getStudyPlanService()->updatePlanDetailLearned([$detail['id']]);
        }
    }

    public function onCourseTaskFinish(Event $event)
    {
        $taskResult = $event->getSubject();
        $biz = $this->getBiz();
        $plan = $this->getStudyPlanService()->getPlanByCourseIdAndUserId($taskResult['courseId'], $biz['user']['id']);
        $details = $this->getStudyPlanService()->searchPlanDetails(['planId' => $plan['id'], 'learned' => 0], [], 0, PHP_INT_MAX);
        $finishedTaskResults = $this->getTaskResultService()->findUserFinishedTaskResultsByCourseId($taskResult['courseId']);
        $finishedTaskResults = array_column($finishedTaskResults, null, 'courseTaskId');
        $finishedDetailIds = [];
        foreach ($details as $detail) {
            if ($this->isDetailAllTasksFinished(array_keys($detail['tasks']), $finishedTaskResults)) {
                $finishedDetailIds[] = $detail['id'];
            }
        }
        $this->getStudyPlanService()->updatePlanDetailLearned($finishedDetailIds);
    }

    public function isDetailAllTasksFinished($taskIds, $finishedTaskResults)
    {
        foreach ($taskIds as $taskId) {
            if (empty($finishedTaskResults[$taskId])) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return TaskResultService
     */
    protected function getTaskResultService()
    {
        return $this->getBiz()->service('Task:TaskResultService');
    }

    /**
     * @return StudyPlanService
     */
    private function getStudyPlanService()
    {
        return $this->getBiz()->service('AgentBundle:StudyPlan:StudyPlanService');
    }
}
