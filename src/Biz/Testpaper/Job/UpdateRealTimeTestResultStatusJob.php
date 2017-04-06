<?php

namespace Biz\Testpaper\Job;

use Biz\Crontab\Service\Job;
use Topxia\Service\Common\ServiceKernel;

class UpdateRealTimeTestResultStatusJob implements Job
{
    public function execute($params)
    {
        if (empty($params['targetId']) || empty($params['targetType']) || $params['targetType'] != 'lesson') {
            return;
        }

        $task = $this->getTaskService()->getTask($params['targetId']);
        $activity = $this->getActivityService()->getActivity($task['activityId'], true);

        if ($activity['mediaType'] != 'testpaper' || empty($activity['ext']['testMode']) || $activity['ext']['testMode'] != 'realTime') {
            return;
        }

        $conditions = array(
            'courseId' => $task['courseId'],
            'lessonId' => $task['activityId'],
        );
        $results = $this->getTestpaperService()->searchTestpaperResults(
            $conditions,
            null,
            0,
            PHP_INT_MAX
        );

        if (empty($results)) {
            return;
        }

        foreach ($results as $result) {
            $this->getTestpaperService()->updateTestpaperResult($result['id'], array('status' => 'reviewing'));
        }
    }

    protected function getCourseService()
    {
        return ServiceKernel::instance()->createService('Course:CourseService');
    }

    protected function getTaskService()
    {
        return ServiceKernel::instance()->createService('Task:TaskService');
    }

    protected function getActivityService()
    {
        return ServiceKernel::instance()->createService('Activity:ActivityService');
    }

    protected function getTestpaperService()
    {
        return ServiceKernel::instance()->createService('Testpaper:TestpaperService');
    }
}
