<?php
namespace Mooc\Service\Course\Job;

use Topxia\Service\Common\ServiceKernel;
use Topxia\Service\Crontab\Job;

class PublishCourseScore implements Job
{
    public function execute($params)
    {
        if (is_array($params) && array_key_exists('courseId', $params)) {
            $courseId    = $params['courseId'];
            $courseScore = $this->getCourseScoreService()->getScoreSettingByCourseId($courseId);

            if (!empty($courseScore) && 'unpublish' == $courseScore['status']) {
                $this->getCourseScoreService()->updateScoreSetting($courseId, array('status' => 'published'));
            }
        }
    }

    protected function getServiceKernel()
    {
        return ServiceKernel::instance();
    }

    protected function getLogService()
    {
        return $this->getServiceKernel()->createService('System.LogService');
    }

    protected function getCourseScoreService()
    {
        return $this->getServiceKernel()->createService("Mooc:Course.CourseScoreService");
    }
}
