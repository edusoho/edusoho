<?php

namespace Biz\Course\Event;

use Biz\Task\Service\TaskResultService;
use Biz\Task\Service\TaskService;
use Biz\System\Service\LogService;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\MemberService;
use Codeages\Biz\Framework\Event\Event;
use Biz\Course\Service\CourseSetService;
use Codeages\PluginBundle\Event\EventSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CourseThreadSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'course.thread.create' => 'onCourseThreadAskVideoThumbnailUpdate',
        );
    }

    public function onCourseThreadAskVideoThumbnailUpdate(Event $event)
    {
        $thread = $event->getSubject();
        if (!empty($thread['videoId'])) {
            $task = $this->getTaskService()->getTask($thread['taskId']);
            $activity = $this->getActivityService()->getActivity($task['activityId'], true);
            $fileId = ($activity['mediaType'] == 'video') ? $activity['ext']['file']['id'] : 0;
            $file = $this->getUploadFileService()->getFile($fileId);
            $result = $this->getMaterialLibService()->getThumbnail($file['globalId'], array('seconds' => $thread['videoAskTime']));
            while ($result['status'] == 'waiting') {
                sleep(3);
                $result = $this->getMaterialLibService()->getThumbnail($file['globalId'], array('seconds' => $thread['videoAskTime']));
            }
            if ($result['status'] == 'success') {
                $this->getCourseThreadService()->updateThread($thread['courseId'], $thread['id'], array('askVideoThumbnail' => $result['url']));
            }
        }
    }

    /**
     * @return \Biz\Course\Service\Impl\ThreadServiceImpl
     */
    protected function getCourseThreadService()
    {
        return $this->getBiz()->service('Course:ThreadService');
    }

    /**
     * @return \Biz\MaterialLib\Service\Impl\MaterialLibServiceImpl
     */
    protected function getMaterialLibService()
    {
        return $this->getBiz()->service('MaterialLib:MaterialLibService');
    }

    /**
     * @return \Biz\File\Service\Impl\UploadFileServiceImpl
     */
    protected function getUploadFileService()
    {
        return $this->getBiz()->service('File:UploadFileService');
    }

    /**
     * @return \Biz\Activity\Service\Impl\ActivityServiceImpl
     */
    protected function getActivityService()
    {
        return $this->getBiz()->service('Activity:ActivityService');
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->getBiz()->service('Course:CourseSetService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->getBiz()->service('Course:CourseService');
    }

    /**
     * @return LogService
     */
    protected function getLogService()
    {
        return $this->getBiz()->service('System:LogService');
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

    /**
     * @return MemberService
     */
    protected function getMemberService()
    {
        return $this->getBiz()->service('Course:MemberService');
    }
}
