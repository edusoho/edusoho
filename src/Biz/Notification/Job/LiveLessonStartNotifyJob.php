<?php

namespace Biz\Notification\Job;

use Biz\Activity\Service\ActivityService;
use Biz\Classroom\Service\ClassroomService;
use Biz\CloudPlatform\QueueJob\PushJob;
use Biz\IM\Service\ConversationService;
use Biz\Task\Service\TaskService;
use Codeages\Biz\Framework\Queue\Service\QueueService;
use Codeages\Biz\Framework\Scheduler\AbstractJob;

class LiveLessonStartNotifyJob extends AbstractJob
{
    public function execute()
    {
        $targetType = $this->args['targetType'];
        $targetId = $this->args['targetId'];
        if ($targetType == 'liveLesson') {
            $lesson = $this->getTaskService()->getTask($targetId);
            $activity = $this->getActivityService()->getActivity($lesson['activityId']);

            $message = '您报名的《'.$lesson['title'].'》课程将于'.date('H:i', $activity['startTime']).'开始直播，点击学习吧';

            $classrooms = $this->getClassroomService()->findClassroomsByCourseId($lesson['courseId']);
            if (empty($classrooms)) {
                $this->pushForClassroomOrCourse($message, $lesson['title'], $lesson['id'], $lesson['courseId']);
            } else {
                foreach ($classrooms as $classroom) {
                    $this->pushForClassroomOrCourse($message, $lesson['title'], $lesson['id'], $lesson['courseId'], $classroom['id']);
                }
            }

            return true;
        }
    }

    protected function pushForClassroomOrCourse($message, $lessonTitle, $lessonId, $courseId, $classroomId = null)
    {
        $conv = array();
        if (empty($classroomId)) {
            $conv = $this->getConversationService()->getConversationByTarget($courseId, 'course-push');
        } else {
            $conv = $this->getConversationService()->getConversationByTarget($classroomId, 'classroom-push');
        }

        $from = array(
            'type' => 'lesson',
            'id' => $lessonId,
        );
        $to = array(
            'type' => 'lesson',
            'id' => 'all',
            'convNo' => $conv['no'],
        );
        $body = array(
            'type' => 'live.start',
            'courseId' => $courseId,
            'lessonId' => $lessonId,
            'lessonTitle' => $lessonTitle,
            'title' => '直播通知',
            'message' => $message,
        );

        if (!empty($classroomId)) {
            $body['classroomId'] = $classroomId;
        }

        $this->createPushJob($from, $to, $body);
    }

    private function createPushJob($from, $to, $body)
    {
        $pushJob = new PushJob(array(
            'from' => $from,
            'to' => $to,
            'body' => $body,
        ));

        $this->getQueueService()->pushJob($pushJob);
    }

    /**
     * @return QueueService
     */
    protected function getQueueService()
    {
        return $this->biz->service('Queue:QueueService');
    }

    /**
     * @return ConversationService
     */
    protected function getConversationService()
    {
        return $this->biz->service('IM:ConversationService');
    }

    /**
     * @return ActivityService
     */
    private function getActivityService()
    {
        return $this->biz->service('Activity:ActivityService');
    }

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->biz->service('Classroom:ClassroomService');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->biz->service('Task:TaskService');
    }
}
