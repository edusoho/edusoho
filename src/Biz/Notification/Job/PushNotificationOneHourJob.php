<?php
namespace Biz\Notification\Job;

use Biz\Crontab\Service\Job;
use Topxia\Service\Common\ServiceKernel;

class PushNotificationOneHourJob implements Job
{
    public function execute($params)
    {
        $targetType = $params['targetType'];
        $targetId   = $params['targetId'];
        if ($targetType == 'lesson') {
            $lesson = $this->getCourseService()->getLesson($targetId);
            $course = $this->getCourseService()->getCourse($lesson['courseId']);

            $lesson['course'] = $course;
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
        $path = "http://{$_SERVER['HTTP_HOST']}/files/{$path}";
        return $path;
    }

    protected function getCourseService()
    {
        return ServiceKernel::instance()->createService('Course:CourseService');
    }

    protected function getCloudDataService()
    {
        return ServiceKernel::instance()->createService('CloudData:CloudDataService');
    }
}
