<?php

namespace Biz\Task\Event;

use Biz\Activity\Service\ActivityService;
use Biz\Activity\Service\HomeworkActivityService;
use Biz\Task\Service\TaskService;
use Codeages\Biz\Framework\Event\Event;
use Codeages\PluginBundle\Event\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ActivitySubscriber extends EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            'activity.start' => 'onActivityStart',
            'activity.doing' => 'onActivityDoing',
            'activity.finish' => 'onActivityFinish',
            'course.task.publish' => 'onCourseTaskPublish',
            'course-set.unlock' => 'onCourseSetUnlock',
        ];
    }

    public function onCourseTaskPublish(Event $event)
    {
        $task = $event->getSubject();
        $activity = $this->getActivityService()->getActivity($task['activityId']);
        if ($activity && 'homework' == $activity['mediaType']) {
            $this->getHomeworkActivityService()->update($activity['mediaId'], ['has_published' => 1]);
        }
    }

    public function onCourseSetUnlock(Event $event)
    {
        $courseSet = $event->getSubject();
        $activities = $this->getActivityService()->findActivitiesByCourseSetIdAndType($courseSet['id'], 'homework');
        if ($activities) {
            $homeworks = [];
            foreach ($activities as $activity) {
                $homeworks[$activity['mediaId']] = [
                    'has_published' => 1,
                ];
            }
            $this->getHomeworkActivityService()->batchUpdate(array_keys($homeworks), $homeworks);
        }
    }

    public function onActivityFinish(Event $event)
    {
        $taskId = $event->getArgument('taskId');

        if ($this->getTaskService()->isFinished($taskId)) {
            $this->getTaskService()->finishTaskResult($taskId);
        }
    }

    public function onActivityStart(Event $event)
    {
        $user = $this->getBiz()->offsetGet('user');
        $task = $event->getArgument('task');
        $this->getTaskService()->startTask($task['id']);
        $this->updateLastLearnTime($task);
    }

    public function updateLastLearnTime($task)
    {
        $user = $this->getBiz()->offsetGet('user');
        $courseMember = $this->getCourseMemberService()->getCourseMember($task['courseId'], $user['id']);
        $this->dispatch('task.show', $courseMember);
    }

    public function onActivityDoing(Event $event)
    {
        $task = $event->getArgument('task');
        $lastTime = $event->getArgument('lastTime');
        $time = time() - $lastTime;
        $learnTimeSec = $this->getTaskService()->getTimeSec('learn');
        if ($time >= $learnTimeSec) {
            $time = $learnTimeSec;
        }

        if (empty($task)) {
            return;
        }
        $this->updateLastLearnTime($task);
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->getBiz()->service('Task:TaskService');
    }

    protected function getTaskResultService()
    {
        return $this->getBiz()->service('Task:TaskResultService');
    }

    protected function getCourseMemberService()
    {
        return $this->getBiz()->service('Course:MemberService');
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->getBiz()->service('Activity:ActivityService');
    }

    /**
     * @return HomeworkActivityService
     */
    protected function getHomeworkActivityService()
    {
        return $this->getBiz()->service('Activity:HomeworkActivityService');
    }

    protected function dispatch($eventName, $subject)
    {
        $event = new Event($subject);

        return $this->getBiz()->offsetGet('dispatcher')->dispatch($eventName, $event);
    }
}
