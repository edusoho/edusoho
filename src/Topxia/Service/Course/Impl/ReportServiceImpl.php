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
        $late30DaysStat = array();
        for ($i = 29; $i >= 0; $i--) {
            $day = date('m-d', strtotime('-'. $i .' days'));
            $late30DaysStat[$day]['day'] = $day;
            $late30DaysStat[$day]['studentNum'] = 0;
            $late30DaysStat[$day]['finishedNum'] = 0;
            $late30DaysStat[$day]['finishedRate'] = 0;
        }

        foreach ($students as $student) {
            $student['createdDay'] = date('m-d', $student['createdTime']);
            $student['finishedDay'] = date('m-d', $student['finishedTime']);
            foreach ($late30DaysStat as $day => &$stat) {
                if (strtotime($student['createdDay']) <= strtotime($day)) {
                    $stat['studentNum']++;
                }

                if ($student['finishedTime'] > 0 && strtotime($student['finishedDay']) <= strtotime($day)) {
                    $stat['finishedNum']++;
                }
            }
        }

        foreach ($late30DaysStat as $day => &$stat) {
            $stat['finishedRate'] = round($stat['finishedNum']/$stat['studentNum'], 3) * 100;
        }

        return $late30DaysStat;
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
