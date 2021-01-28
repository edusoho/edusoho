<?php

namespace Biz\Notification\Job;

use Biz\CloudPlatform\QueueJob\PushJob;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\System\Service\SettingService;
use Biz\Task\Service\TaskService;
use Codeages\Biz\Framework\Queue\Service\QueueService;
use Codeages\Biz\Framework\Scheduler\AbstractJob;

class PushNotificationOneHourJob extends AbstractJob
{
    public function execute()
    {
        $targetType = $this->args['targetType'];
        $targetId = $this->args['targetId'];
        if ($targetType == 'lesson') {
            $lesson = $this->getTaskService()->getTask($targetId);
            $course = $this->getCourseService()->getCourse($lesson['courseId']);
            $courseSet = $this->getCourseSetService()->getCourseSet($lesson['fromCourseSetId']);

            $lesson['course'] = $course;
            $lesson['courseSet'] = $courseSet;

            $from = array(
                'type' => 'course',
                'id' => $course['id'],
            );

            $to = array(
                'type' => 'course',
                'id' => $course['id'],
                'convNo' => $this->getConvNo(),
            );

            $body = array(
                'type' => 'live.notify',
                'id' => $lesson['id'],
                'lessonType' => $lesson['type'],
                'title' => "《{$lesson['title']}》",
                'message' => "直播课《{$lesson['title']}》即将开课",
            );

            $this->createPushJob($from, $to, $body);
        }
    }

    private function getConvNo()
    {
        $imSetting = $this->getSettingService()->get('app_im', array());
        $convNo = isset($imSetting['convNo']) && !empty($imSetting['convNo']) ? $imSetting['convNo'] : '';

        return $convNo;
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
     * @return CourseService
     */
    private function getCourseService()
    {
        return $this->biz->service('Course:CourseService');
    }

    /**
     * @return CourseSetService
     */
    private function getCourseSetService()
    {
        return $this->biz->service('Course:CourseSetService');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->biz->service('Task:TaskService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->biz->service('System:SettingService');
    }

    /**
     * @return QueueService
     */
    protected function getQueueService()
    {
        return $this->biz->service('Queue:QueueService');
    }
}
