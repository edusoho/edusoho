<?php

namespace Biz\Notification\Job;

use Biz\CloudPlatform\QueueJob\PushJob;
use Biz\System\Service\SettingService;
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

    protected function getFileUrl($path)
    {
        if (empty($path)) {
            return $path;
        }
        $path = str_replace('public://', '', $path);
        $path = str_replace('files/', '', $path);

        // TODO: fix command方式下
        $path = "http://{$_SERVER['HTTP_HOST']}/files/{$path}";

        return $path;
    }

    private function getCourseService()
    {
        return $this->biz->service('Course:CourseService');
    }

    private function getCourseSetService()
    {
        return $this->biz->service('Course:CourseSetService');
    }

    protected function getTaskService()
    {
        return $this->biz->service('Task:TaskService');
    }

    protected function getCloudDataService()
    {
        return $this->biz->service('CloudData:CloudDataService');
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
