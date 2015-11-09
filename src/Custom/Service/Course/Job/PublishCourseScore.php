<?php
namespace Custom\Service\Course\Job;

use Topxia\Service\Common\ServiceKernel;
use Topxia\Service\Crontab\Job;

class PublishCourseScore implements Job
{
    public function execute($params)
    {
        if (is_array($params) && array_key_exists('courseId', $params)) {
            $courseId = $params['courseId'];
            $courseScore = $this->getCourseScoreService()->getScoreSettingByCourseId($courseId);
            if (!empty($courseScore) && $courseScore['status'] == 'unpublish') {
                $this->getCourseScoreService()->updateScoreSetting($courseId, array(
                    'status'      => 'published',
                    'publishTime' => time()));
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
        return $this->getServiceKernel()->createService("Custom:Course.CourseScoreService");
    }
}
