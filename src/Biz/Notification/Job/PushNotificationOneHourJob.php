<?php

namespace Biz\Notification\Job;

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
            $this->pushCloud('lesson.live_notify', $lesson);
        }
    }

    protected function pushCloud($eventName, array $data, $level = 'normal')
    {
        return $this->getCloudDataService()->push('school.'.$eventName, $data, time(), $level);
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
}
