<?php

namespace Biz\Task\Event;

use Biz\Activity\Service\ActivityService;
use Biz\Activity\Service\TestpaperActivityService;
use Biz\Task\Service\TaskService;
use Biz\Task\Service\TaskResultService;
use Biz\User\Service\UserService;
use Codeages\Biz\Framework\Event\Event;
use Codeages\PluginBundle\Event\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class TestpaperSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'exam.reviewed' => 'onTestPaperReviewed',
        );
    }

    public function onTestPaperReviewed(Event $event)
    {
        $testpaperResult = $event->getSubject();

        $task = $this->getTaskService()->getTaskByCourseIdAndActivityId($testpaperResult['courseId'], $testpaperResult['lessonId']);
        $activity = $this->getActivityService()->getActivity($testpaperResult['lessonId']);
        $testpaperActivity = $this->getTestpaperActivityService()->getActivity($activity['mediaId']);

        if ($testpaperResult['score'] >= $testpaperActivity['finishCondition']['finishScore']) {
            $this->finishTaskResult($task['id'], $testpaperResult['userId']);
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
        $this->dispatch('course.task.finish', new Event($taskResult, array('user' => $user)));
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
}
