<?php

namespace AgentBundle\Biz\StudyPlan\Event;

use AgentBundle\Biz\AgentConfig\Service\AgentConfigService;
use AgentBundle\Biz\StudyPlan\Service\StudyPlanService;
use AgentBundle\Workflow\TaskScheduler;
use AgentBundle\Workflow\TaskTrait;
use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\DateToolkit;
use Biz\Activity\Service\ActivityService;
use Biz\AI\Service\AIService;
use Biz\AppPush\Service\AppPushService;
use Biz\Task\Service\TaskService;
use Biz\User\Service\UserService;
use Codeages\Biz\Framework\Event\Event;
use Codeages\Biz\Framework\Event\EventSubscriber;

class StudyPlanEventSubscriber extends EventSubscriber
{
    use TaskTrait;

    public static function getSubscribedEvents()
    {
        return [
            'course.quit' => 'onCourseMemberDelete',
            'activity.doing' => 'onActivityDoing',
            'course.task.finish' => 'onCourseTaskFinish',
            'course.task.publish' => 'onCourseTaskPublish',
            'course.task.delete' => 'onCourseTaskDelete',
            'user.lock' => 'onUserLock',
        ];
    }

    public function onCourseMemberDelete(Event $event)
    {
        $course = $event->getSubject();
        $plan = $this->getStudyPlanService()->getPlanByCourseIdAndUserId($course['id'], $event->getArgument('userId'));
        if (empty($plan)) {
            return;
        }
        $this->getStudyPlanService()->deletePlan($plan['id']);
        $this->getStudyPlanService()->deletePlanTasksByPlanId($plan['id']);
    }

    public function onActivityDoing(Event $event)
    {
        $task = $event->getArgument('task');
        $biz = $this->getBiz();
        $plan = $this->getStudyPlanService()->getPlanByCourseIdAndUserId($task['courseId'], $biz['user']['id']);
        if (empty($plan)) {
            return;
        }
        $planTasks = $this->getStudyPlanService()->searchPlanTasks(['planId' => $plan['id'], 'taskId' => $task['id'], 'learned' => 0], ['studyDate' => 'ASC'], 0, PHP_INT_MAX);
        if (empty($planTasks)) {
            return;
        }
        $activity = $event->getSubject();
        if (in_array($activity['finishType'], ['end', 'watchTime'])) {
            $duration = $event->hasArgument('watchTime') ? $event->getArgument('watchTime') : 0;
        } else {
            $duration = $event->hasArgument('duration') ? $event->getArgument('duration') : 0;
        }
        $learnedTaskIds = [];
        $hasTodayTaskLearned = false;
        $hasLaterTaskLearned = false;
        foreach ($planTasks as $planTask) {
            if (($planTask['learnedDuration'] + $duration) >= $planTask['targetDuration']) {
                $this->getStudyPlanService()->wavePlanTaskLearnedDuration($planTask['id'], $planTask['targetDuration'] - $planTask['learnedDuration']);
                $duration -= $planTask['targetDuration'] - $planTask['learnedDuration'];
                $learnedTaskIds[] = $planTask['id'];
                if (date('Y-m-d') == $planTask['studyDate']) {
                    $hasTodayTaskLearned = true;
                }
                if (date('Y-m-d') < $planTask['studyDate']) {
                    $hasLaterTaskLearned = true;
                }
            } else {
                $this->getStudyPlanService()->wavePlanTaskLearnedDuration($planTask['id'], $duration);
                break;
            }
        }
        $this->getStudyPlanService()->markPlanTaskLearned($learnedTaskIds);
        if ($hasTodayTaskLearned) {
            $this->pushMessageIfNecessary($plan);
        }
        if ($hasLaterTaskLearned) {
            $planTasks = $this->getStudyPlanService()->searchPlanTasks(['planId' => $plan['id'], 'learned' => 0], ['studyDate' => 'ASC', 'id' => 'ASC'], 0, PHP_INT_MAX);
            $courseTasks = $this->getTaskService()->findTasksByIds(array_column($planTasks, 'taskId'));
            $courseTasks = array_column($courseTasks, null, 'id');
            $taskScheduler = new TaskScheduler();
            $plan['dailyLearnDuration'] = $plan['dailyAvgTime'] / 60;
            $this->getStudyPlanService()->generatePlanTasks($plan['id'], $taskScheduler->schedule($plan, $this->makeTasks($planTasks, $courseTasks)));
        }
    }

    public function onCourseTaskFinish(Event $event)
    {
        $taskResult = $event->getSubject();
        $biz = $this->getBiz();
        $plan = $this->getStudyPlanService()->getPlanByCourseIdAndUserId($taskResult['courseId'], $biz['user']['id']);
        if (empty($plan)) {
            return;
        }
        $planTasks = $this->getStudyPlanService()->searchPlanTasks(['planId' => $plan['id'], 'taskId' => $taskResult['courseTaskId'], 'learned' => 0], [], 0, PHP_INT_MAX);
        if (empty($planTasks)) {
            return;
        }
        $this->getStudyPlanService()->markPlanTaskLearned(array_column($planTasks, 'id'));
        $hasTodayTaskLearned = false;
        $hasLaterTaskLearned = false;
        foreach ($planTasks as $planTask) {
            if (date('Y-m-d') == $planTask['studyDate']) {
                $hasTodayTaskLearned = true;
            }
            if (date('Y-m-d') < $planTask['studyDate']) {
                $hasLaterTaskLearned = true;
            }
        }
        if ($hasTodayTaskLearned) {
            $this->pushMessageIfNecessary($plan);
        }
        if ($hasLaterTaskLearned) {
            $planTasks = $this->getStudyPlanService()->searchPlanTasks(['planId' => $plan['id'], 'learned' => 0], ['studyDate' => 'ASC', 'id' => 'ASC'], 0, PHP_INT_MAX);
            $courseTasks = $this->getTaskService()->findTasksByIds(array_column($planTasks, 'taskId'));
            $courseTasks = array_column($courseTasks, null, 'id');
            $taskScheduler = new TaskScheduler();
            $plan['dailyLearnDuration'] = $plan['dailyAvgTime'] / 60;
            $this->getStudyPlanService()->generatePlanTasks($plan['id'], $taskScheduler->schedule($plan, $this->makeTasks($planTasks, $courseTasks)));
        }
    }

    public function onCourseTaskPublish(Event $event)
    {
        $task = $event->getSubject();
        $activity = $this->getActivityService()->getActivity($task['activityId']);
        if (!in_array($activity['mediaType'], ['text', 'video', 'audio', 'live', 'doc', 'ppt', 'testpaper', 'replay'])) {
            return;
        }
        if (empty($this->filterSchedulableActivities([$activity]))) {
            return;
        }
        $plans = $this->getStudyPlanService()->findActivePlansByCourseId($task['courseId']);
        if (empty($plans)) {
            return;
        }
        $planIds = array_column($plans, 'id');
        $planTasks = $this->getStudyPlanService()->searchPlanTasks(['planIds' => $planIds, 'taskId' => $task['id']], [], 0, PHP_INT_MAX);
        $planIds = array_values(array_diff($planIds, array_unique(array_column($planTasks, 'planId'))));
        $planTasks = $this->getStudyPlanService()->searchPlanTasks(['planIds' => $planIds, 'learned' => 0], ['studyDate' => 'ASC', 'id' => 'ASC'], 0, PHP_INT_MAX);
        $courseTasks = $this->getTaskService()->findTasksByIds(array_column($planTasks, 'taskId'));
        $courseTasks = array_column($courseTasks, null, 'id');
        $planTasksGroup = ArrayToolkit::group($planTasks, 'planId');
        $taskScheduler = new TaskScheduler();
        $plans = array_column($plans, null, 'id');
        $task['duration'] = $this->calculateDuration($activity);
        foreach ($planTasksGroup as $planId => $tasks) {
            $plan = $plans[$planId];
            $plan['dailyLearnDuration'] = $plan['dailyAvgTime'] / 60;
            $newTasks = $this->makeTasks($tasks, $courseTasks);
            $newTasks[] = $task;
            $this->getStudyPlanService()->generatePlanTasks($planId, $taskScheduler->schedule($plan, $newTasks));
        }
    }

    public function onCourseTaskDelete(Event $event)
    {
        $task = $event->getSubject();
        $planTasks = $this->getStudyPlanService()->searchPlanTasks(['courseId' => $task['courseId'], 'taskId' => $task['id'], 'learned' => 0], [], 0, PHP_INT_MAX);
        if (empty($planTasks)) {
            return;
        }
        $plans = $this->getStudyPlanService()->findActivePlansByIds(array_column($planTasks, 'planId'));
        $planTasks = $this->getStudyPlanService()->searchPlanTasks(['planIds' => array_column($plans, 'id'), 'excludeTaskId' => $task['id'], 'learned' => 0], ['studyDate' => 'ASC', 'id' => 'ASC'], 0, PHP_INT_MAX);
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

    public function onUserLock(Event $event)
    {
        $user = $event->getSubject();
        $this->getAppPushService()->unbindDevice($user['id']);
    }

    private function pushMessageIfNecessary($plan)
    {
        $agentConfig = $this->getAgentConfigService()->getAgentConfigByCourseId($plan['courseId']);
        if (empty($agentConfig['isActive'])) {
            return;
        }
        $planTasks = $this->getStudyPlanService()->searchPlanTasks(['planId' => $plan['id'], 'studyDate' => date('Y-m-d'), 'learned' => 0], [], 0, PHP_INT_MAX);
        if (!empty($planTasks)) {
            return;
        }
        $user = $this->getUserService()->getUser($plan['userId']);
        $content = "hi，{$user['nickname']}同学，恭喜完成今日学习，快快放松休息一下吧~";
        $nextStudyDate = $this->getNextStudyDate($plan['id']);
        if (!empty($nextStudyDate)) {
            $nextStudyDate = new \DateTime($nextStudyDate);
            $zhWeekday = DateToolkit::convertToZHWeekday($nextStudyDate->format('N'));
            $content .= "  \n下次学习在 {$nextStudyDate->format('Y年m月d日')}（{$zhWeekday}），届时我来提醒你哦~";
        }
        try {
            $this->getAIService()->pushMessage([
                'domainId' => $agentConfig['domainId'],
                'userId' => $user['id'],
                'contentType' => 'text',
                'content' => $content,
            ]);
        } catch (\Exception $e) {
            $this->getBiz()['logger']->error('push message error: '.$e->getMessage());
        }
    }

    private function getNextStudyDate($planId)
    {
        $nextDetails = $this->getStudyPlanService()->searchPlanTasks(['planId' => $planId, 'learned' => 0], ['studyDate' => 'ASC'], 0, 1);

        return $nextDetails[0]['studyDate'] ?? '';
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
        return $this->getBiz()->service('Task:TaskService');
    }

    /**
     * @return ActivityService
     */
    private function getActivityService()
    {
        return $this->getBiz()->service('Activity:ActivityService');
    }

    /**
     * @return UserService
     */
    private function getUserService()
    {
        return $this->getBiz()->service('User:UserService');
    }

    /**
     * @return AppPushService
     */
    private function getAppPushService()
    {
        return $this->getBiz()->service('AppPush:AppPushService');
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
