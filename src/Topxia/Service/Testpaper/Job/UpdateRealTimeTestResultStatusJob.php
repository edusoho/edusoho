<?php


namespace Topxia\Service\Testpaper\Job;


use Topxia\Service\Common\ServiceKernel;
use Topxia\Service\Crontab\Job;

class UpdateRealTimeTestResultStatusJob implements Job
{
    public function execute($params)
    {
        if(empty($params['targetId']) || empty($params['targetType']) || $params['targetType'] != 'lesson'){
            return;
        }

        $lesson = $this->getCourseService()->getLesson($params['targetId']);

        if($lesson['type'] != 'testpaper' || empty($lesson['testMode']) || $lesson['testMode'] != 'realTime'){
            return;
        }

        $fields['status'] = 'reviewing';
        $this->getTestpaperService()->updateTestResultsByLessonId($lesson['id'], $fields);

    }

    protected function getCourseService()
    {
        return ServiceKernel::instance()->createService('Course.CourseService');
    }

    protected function getTestpaperService()
    {
        return ServiceKernel::instance()->createService('Testpaper.TestpaperService');
    }
}