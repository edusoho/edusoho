<?php
namespace Topxia\Service\Course\Job;

use Topxia\Service\Crontab\Job;
use Topxia\Service\Common\ServiceKernel;

class LiveLessonStartNotifyJob implements Job
{
    public function execute($params)
    {
        $targetType = $params['targetType'];
        $targetId   = $params['targetId'];
        if ($targetType == 'live_lesson') {
            $lesson = $this->getCourseService()->getLesson($targetId);
            $course = $this->getCourseService()->getCourse($lesson['courseId']);

            $lesson['course'] = $course;
            //获取课程的学员列表，发送通知
            //FXIME
            //1. 下面的分页查询应确保查询出所有学员
            //2. 数据量可能很大，应进行过滤，只传递所需信息，但同时也要保证一定的通用性 @苏菊
            $lession['students'] = $this->getCourseService()->findCourseStudents($lesson['courseId'], 0, 1000);
            $this->pushCloud('lesson.live_notify', $lesson);
        }
    }

    protected function pushCloud($eventName, array $data, $level = 'normal')
    {
        return $this->getCloudDataService()->push('school.'.$eventName, $data, time(), $level);
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
