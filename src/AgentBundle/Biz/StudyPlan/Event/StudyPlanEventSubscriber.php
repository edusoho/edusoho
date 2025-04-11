<?php

namespace AgentBundle\Biz\StudyPlan\Event;

use AgentBundle\Biz\AgentConfig\Service\AgentConfigService;
use AgentBundle\Biz\StudyPlan\Service\StudyPlanService;
use AppBundle\Common\DateToolkit;
use Biz\AI\Service\AIService;
use Biz\Task\Service\TaskResultService;
use Biz\User\Service\UserService;
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
            $this->pushMessageIfNecessary($plan, $detail);
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

    private function pushMessageIfNecessary($plan, $detail)
    {
        $agentConfig = $this->getAgentConfigService()->getAgentConfigByCourseId($plan['courseId']);
        if (empty($agentConfig['isActive'])) {
            return;
        }
        $user = $this->getUserService()->getUser($plan['userId']);
        $content = "hi，{$user['nickname']}同学，恭喜完成今日学习，快快放松休息一下吧~";
        $nextStudyDate = $this->getNextStudyDate($detail);
        if (!empty($nextStudyDate)) {
            $nextStudyDate = new \DateTime($nextStudyDate);
            $zhWeekday = DateToolkit::convertToZHWeekday($nextStudyDate->format('N'));
            $content .= "  \n下次学习在 {$nextStudyDate->format('Y年m月d日')}（{$zhWeekday}），届时我来提醒你哦~";
        }
        $this->getAIService()->pushMessage([
            'domainId' => $agentConfig['domainId'],
            'userId' => $user['id'],
            'contentType' => 'text',
            'content' => $content,
        ]);
    }

    private function getNextStudyDate($detail)
    {
        $nextDetails = $this->getStudyPlanService()->searchPlanDetails(['planId' => $detail['planId'], 'learned' => 0], ['studyDate' => 'ASC'], 0, 1);

        return $nextDetails[0]['studyDate'] ?? '';
    }

    private function isDetailAllTasksFinished($taskIds, $finishedTaskResults)
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
    private function getTaskResultService()
    {
        return $this->getBiz()->service('Task:TaskResultService');
    }

    /**
     * @return UserService
     */
    private function getUserService()
    {
        return $this->getBiz()->service('User:UserService');
    }

    /**
     * @return StudyPlanService
     */
    private function getStudyPlanService()
    {
        return $this->getBiz()->service('AgentBundle:StudyPlan:StudyPlanService');
    }

    /**
     * @return AgentConfigService
     */
    private function getAgentConfigService()
    {
        return $this->getBiz()->service('AgentBundle:AgentConfig:AgentConfigService');
    }

    /**
     * @return AIService
     */
    private function getAIService()
    {
        return $this->getBiz()->service('AI:AIService');
    }
}
