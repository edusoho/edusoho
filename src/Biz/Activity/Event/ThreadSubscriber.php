<?php

namespace Biz\Activity\Event;

use Topxia\Common\ArrayToolkit;
use Codeages\Biz\Framework\Event\Event;
use Codeages\PluginBundle\Event\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ThreadSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'course.thread.create'      => 'onThreadCreate',
            'course.thread.post.create' => 'onPostCreate'
        );
    }

    public function onThreadCreate(Event $event)
    {
        $thread = $event->getSubject();
        $this->getLogger('ThreadSubscriber')->debug('onThreadCreate : ', $thread);
        $this->triggerActivitiesByCourseId($thread['courseId']);
    }

    public function onPostCreate(Event $event)
    {
        $post = $event->getSubject();
        $this->getLogger('ThreadSubscriber')->debug('onPostCreate : ', $event->getSubject());
        $this->triggerActivitiesByCourseId($post['courseId']);
    }

    protected function triggerActivitiesByCourseId($courseId)
    {
        $activities = $this->getActivityService()->findActivitiesByCourseIdAndType($courseId, 'discuss');
        if (empty($activities)) {
            return;
        }
        $activityIds = ArrayToolkit::column($activities, 'id');

        foreach ($activityIds as $activityId) {
            $task = $this->getTaskService()->getTaskByCourseIdAndActivityId($courseId, $activityId);
            if ($task) {
                $taskResult = $this->getTaskResultService()->getUserTaskResultByTaskId($task['id']);
                if (empty($taskResult) || $taskResult['status'] == 'finish') {
                    //如果任务尚未开始，或者已经完成则不必触发
                    continue;
                }
                $this->getActivityService()->trigger($activityId, 'finish', array('taskId' => $task['id']));
            }
        }
    }

    protected function getActivityService()
    {
        return $this->getBiz()->service('Activity:ActivityService');
    }

    protected function getTaskService()
    {
        return $this->getBiz()->service('Task:TaskService');
    }

    protected function getLogger($name)
    {
        $biz = $this->getBiz();
        return $biz['logger'];
    }
}
