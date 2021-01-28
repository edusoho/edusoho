<?php

namespace Biz\Task\Event;

use Biz\Activity\Service\ActivityService;
use Biz\Activity\Service\TestpaperActivityService;
use Biz\Task\Service\TaskResultService;
use Biz\Task\Service\TaskService;
use Biz\User\Service\UserService;
use Codeages\Biz\Framework\Event\Event;
use Codeages\Biz\ItemBank\Answer\Service\AnswerRecordService;
use Codeages\PluginBundle\Event\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class TestpaperSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            'answer.finished' => 'onAnswerFinished',
        ];
    }

    public function onAnswerFinished(Event $event)
    {
        $answerReport = $event->getSubject();

        $testpaperActivity = $this->getTestpaperActivityService()->getActivityByAnswerSceneId($answerReport['answer_scene_id']);
        if (empty($testpaperActivity)) {
            return;
        }

        $activity = $this->getActivityService()->getByMediaIdAndMediaType($testpaperActivity['id'], 'testpaper');
        if (empty($activity)) {
            return;
        }

        $task = $this->getTaskService()->getTaskByCourseIdAndActivityId($activity['fromCourseId'], $activity['id']);
        if ('score' == $activity['finishType'] && $answerReport['score'] >= $testpaperActivity['finishCondition']['finishScore']) {
            $answerRecord = $this->getAnswerRecordService()->get($answerReport['answer_record_id']);
            $this->finishTaskResult($task['id'], $answerRecord['user_id']);
        }
    }

    protected function finishTaskResult($taskId, $userId)
    {
        $taskResult = $this->getTaskResultService()->getTaskResultByTaskIdAndUserId($taskId, $userId);

        if (empty($taskResult) || 'finish' === $taskResult['status']) {
            return;
        }

        $update['updatedTime'] = time();
        $update['status'] = 'finish';
        $update['finishedTime'] = time();
        $taskResult = $this->getTaskResultService()->updateTaskResult($taskResult['id'], $update);

        $user = $this->getUserService()->getUser($userId);
        $this->dispatch('course.task.finish', new Event($taskResult, ['user' => $user]));
    }

    protected function dispatch($eventName, $event)
    {
        return $this->getBiz()->offsetGet('dispatcher')->dispatch($eventName, $event);
    }

    /**
     * @return TaskResultService
     */
    protected function getTaskResultService()
    {
        return $this->getBiz()->service('Task:TaskResultService');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->getBiz()->service('Task:TaskService');
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->getBiz()->service('Activity:ActivityService');
    }

    /**
     * @return TestpaperActivityService
     */
    protected function getTestpaperActivityService()
    {
        return $this->getBiz()->service('Activity:TestpaperActivityService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->getBiz()->service('User:UserService');
    }

    /**
     * @return AnswerRecordService
     */
    protected function getAnswerRecordService()
    {
        return $this->getBiz()->service('ItemBank:Answer:AnswerRecordService');
    }
}
