<?php

namespace Biz\Activity\Event;

use Biz\Activity\Service\ActivityService;
use Biz\Task\Service\TaskResultService;
use Biz\Task\Service\TaskService;
use AppBundle\Common\ArrayToolkit;
use Codeages\Biz\Framework\Event\Event;
use Codeages\PluginBundle\Event\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ThreadSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'course.thread.create' => 'onThreadCreate',
            'course.thread.post.create' => 'onPostCreate',
        );
    }

    public function onThreadCreate(Event $event)
    {
        $thread = $event->getSubject();
        $this->getLogger('ThreadSubscriber')->debug('onThreadCreate : ', $thread);
        $this->triggerActivitiesAndFinishTaskByCourseId($thread['courseId']);
    }

    public function onPostCreate(Event $event)
    {
        $post = $event->getSubject();
        $this->getLogger('ThreadSubscriber')->debug('onPostCreate : ', $event->getSubject());
        $this->triggerActivitiesAndFinishTaskByCourseId($post['courseId']);
    }

    protected function triggerActivitiesAndFinishTaskByCourseId($courseId)
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
                if (empty($taskResult) || 'finish' == $taskResult['status']) {
                    //如果任务尚未开始，或者已经完成则不必触发
                    continue;
                }
                $this->getActivityService()->trigger($activityId, 'finish', array('taskId' => $task['id']));
                $this->getTaskService()->finishTask($task['id']);
            }
        }
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->getBiz()->service('Activity:ActivityService');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->getBiz()->service('Task:TaskService');
    }

    /**
     * @return TaskResultService
     */
    protected function getTaskResultService()
    {
        return $this->getBiz()->service('Task:TaskResultService');
    }

    protected function getLogger($name)
    {
        $biz = $this->getBiz();

        return $biz['logger'];
    }
}
