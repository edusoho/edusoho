<?php
namespace Topxia\Service\Course\Impl;

use Topxia\Common\ArrayToolkit;
use Topxia\Service\Common\BaseService;
use Topxia\Service\Course\ReportService;

class ReportServiceImpl extends BaseService implements ReportService
{
    public function summary($courseId)
    {
        $summary = array(
            'studentNum' => 0,
            'noteNum' => 0,
            'askNum' => 0,
            'discussionNum' => 0,
            'finishedNum' => 0,//完成人数
        );

        $course = $this->getCourseService()->getCourse($courseId);
        $summary['studentNum'] = $course['studentNum'];
        $summary['noteNum'] = $course['noteNum'];
        $summary['askNum'] = $this->getThreadService()->searchThreadCount(array('courseId' => $courseId, 'type' => 'question'));
        $summary['discussionNum'] = $this->getThreadService()->searchThreadCount(array('courseId' => $courseId, 'type' => 'discussion'));
        $summary['finishedNum'] = $this->getCourseService()->searchMemberCount(array('courseId' => $courseId, 'isLearned' => 1));

        return $summary;
    }

    public function getLateMonthLearndData($courseId)
    {
        $students = $this->getCourseService()->findCourseStudents($courseId, 0, PHP_INT_MAX);
        $late30Days = array();
        for ($i = 0; $i < 30; $i++) {
            $day = date("m-d", strtotime('-'. $i .' days'));
            $late30Days[$day]['day'] = $day;
        }

        foreach ($students as $student) {

        }
    }

    protected function getCourseService()
    {
        return $this->createService('Course.CourseService');
    }

    protected function getThreadService()
    {
        return $this->createService('Course.ThreadService');
    }
}
