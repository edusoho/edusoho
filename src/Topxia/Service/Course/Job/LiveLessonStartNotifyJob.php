<?php
namespace Topxia\Service\Course\Job;

use Topxia\Service\Crontab\Job;
use Topxia\Service\Common\ServiceKernel;

class LiveLessonStartNotifyJob implements Job
{
    public function execute($params)
    {

    }

    private function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    private function getServiceKernel()
    {
        return ServiceKernel::instance();
    }

}