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
        $summary['finishedNum'] = $this->getCourseService()->searchMemberCount(array('courseId' => $courseId, 'isLearned' => 1 , 'role' => 'student'));

        if ($summary['studentNum']) {
            $summary['finishedRate'] = round($summary['finishedNum']/$summary['studentNum'], 3) * 100;
        } else {
            $summary['finishedRate'] = 0;
        }
        return $summary;
    }

    public function getLateMonthLearndData($courseId)
    {
        $students = $this->getCourseService()->findCourseStudents($courseId, 0, PHP_INT_MAX);
        $late30DaysStat = array();
        for ($i = 29; $i >= 0; $i--) {
            $day = date('Y-m-d', strtotime('-'. $i .' days'));
            $late30DaysStat[$day]['day'] = date('m-d', strtotime('-'. $i .' days'));
            $late30DaysStat[$day]['studentNum'] = 0;
            $late30DaysStat[$day]['finishedNum'] = 0;
            $late30DaysStat[$day]['finishedRate'] = 0;
        }

        foreach ($students as $student) {
            $student['createdDay'] = date('Y-m-d', $student['createdTime']);
            $student['finishedDay'] = date('Y-m-d', $student['finishedTime']);
         
            foreach ($late30DaysStat as $day => &$stat) {
                if ($student['createdDay'] == $day || strtotime($student['createdDay']) < strtotime(date('Y-m-d', strtotime('- 29 days')))) {
                    $stat['studentNum']++;
                }

                if ($student['isLearned'] && $student['finishedTime'] > 0 && ($student['finishedDay'] == $day ||  strtotime($student['finishedDay']) < strtotime(date('Y-m-d', strtotime('- 29 days'))))) {
                    $stat['finishedNum']++;
                }
            }
        }

        foreach ($late30DaysStat as $day => &$stat) {
            if ($stat['studentNum']) {
                $stat['finishedRate'] = round($stat['finishedNum']/$stat['studentNum'], 3) * 100;
            } else {
                $stat['studentNum'] = 0;
            }
            
        }

        return $late30DaysStat;
    }

    public function getCourseLessonLearnStat($courseId)
    {
        $lessons = $this->getCourseService()->getCourseLessons($courseId);
        foreach ($lessons as $lessonId => &$lesson) {
            $lesson['alias'] = '课时'.$lesson['seq'];
            $lesson['finishedNum'] = $this->getCourseService()->searchLearnCount(array('lessonId' => $lessonId, 'status' => 'finished'));
            $lesson['learnNum'] = $this->getCourseService()->findLearnsCountByLessonId($lessonId);

            if ($lesson['learnNum']) {
                $lesson['finishedRate'] = round($lesson['finishedNum']/$lesson['learnNum'], 3) * 100;
            } else {
                $lesson['finishedRate'] = 0;
            }
        }

        return $lessons;
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
