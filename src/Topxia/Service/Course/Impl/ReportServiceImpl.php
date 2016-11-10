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

        $summary['studentNum'] = $this->getCourseService()->searchMemberCount(array('courseId' => $courseId, 'role' => 'student'));
        $summary['noteNum'] = $this->getCourseNoteService()->searchNoteCount(array('courseId' => $courseId));
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
        $now = time();
        $lateMonthData = $this->getLateMonthData($courseId, $now);
        $before30DaysData = $this->getAMonthAgoStatCount($courseId, $now);
        $late30DaysStat = array();
        for ($i = 29; $i >= 0; $i--) {
            $day = date('Y-m-d', strtotime('-'. $i .' days'));
            $late30DaysStat[$day]['day'] = date('m-d', strtotime('-'. $i .' days'));
            $late30DaysStat[$day]['studentNum'] = $before30DaysData['studentNum'];
            $late30DaysStat[$day]['finishedNum'] = $before30DaysData['finishedNum'];
            $late30DaysStat[$day]['finishedRate'] = $before30DaysData['finishedRate'];
            $late30DaysStat[$day]['noteNum'] = $before30DaysData['noteNum'];
            $late30DaysStat[$day]['askNum'] = $before30DaysData['askNum'];
            $late30DaysStat[$day]['discussionNum'] = $before30DaysData['discussionNum'];
        }

        //隐藏笔记、提问、讨论的历史数据
        $this->countStudentsData($lateMonthData['students'], $late30DaysStat);
        //$this->countNotesData($lateMonthData['notes'], $late30DaysStat);
        //$this->countAsksData($lateMonthData['asks'], $late30DaysStat);
        //$this->countDiscussionsData($lateMonthData['discussions'], $late30DaysStat);

        return $late30DaysStat;
    }

    public function getCourseLessonLearnStat($courseId)
    {
        $lessons = $this->getCourseService()->getCourseLessons($courseId);
        usort($lessons, function ($lesson1, $lesson2) {
            return $lesson1['number'] < $lesson2['number'];
        });
        $teachers = $this->getCourseService()->findCourseTeachers($courseId);
        $excludeUserIds = ArrayToolkit::column($teachers, 'userId');
        foreach ($lessons as &$lesson) {
            $lesson['alias'] = '课时'.$lesson['number'];
            $lesson['finishedNum'] = $this->getCourseService()->searchLearnCount(array('lessonId' => $lesson['id'], 'excludeUserIds' => $excludeUserIds, 'status' => 'finished'));
            $lesson['learnNum'] = $this->getCourseService()->searchLearnCount(array('lessonId' => $lesson['id'], 'excludeUserIds' => $excludeUserIds, 'status' => 'learning'));

            if ($lesson['learnNum']) {
                $lesson['finishedRate'] = round($lesson['finishedNum']/$lesson['learnNum'], 3) * 100;
            } else {
                $lesson['finishedRate'] = 0;
            }
        }

        return $lessons;
    }

    /**
     * 获取30天以前的数据
     */
    private function getAMonthAgoStatCount($courseId, $now)
    {
        $role = 'student';
        $startTimeLessThan = strtotime('- 29 days', $now);
        $result = array();
        //学员数
        $result['studentNum'] = $this->getCourseService()->searchMemberCount(array('courseId' => $courseId,
            'role' => $role,
            'startTimeLessThan' => $startTimeLessThan
        ));
        //完课数
        $result['finishedNum'] = $this->getCourseService()->searchMemberCount(array(
            'courseId' => $courseId,
            'role' => $role,
            'isLearned' => 1,
            'startTimeLessThan' => $startTimeLessThan
        ));
        //完成率
        if ($result['studentNum']) {
            $result['finishedRate'] = round($result['finishedNum']/$result['studentNum'], 3) * 100;
        } else {
            $result['finishedRate'] = 0;
        }

        //笔记数
        $result['noteNum'] = $this->getCourseNoteService()->searchNoteCount(array(
            'courseId' => $courseId,
            'startTimeLessThan' => $startTimeLessThan
        ));

        //问题数
        $result['askNum'] = $this->getThreadService()->searchThreadCount(array(
            'courseId' => $courseId,
            'type' => 'question',
            'startTimeLessThan' => $startTimeLessThan
        ));

        //讨论数
        $result['discussionNum'] = $this->getThreadService()->searchThreadCount(array(
            'courseId' => $courseId,
            'type' => 'discussion',
            'startTimeLessThan' => $startTimeLessThan
        ));

        return $result;
    }


    private function countStudentsData($students, &$late30DaysStat)
    {
        foreach ($students as $student) {
            $student['createdDay'] = date('Y-m-d', $student['createdTime']);
            $student['finishedDay'] = date('Y-m-d', $student['finishedTime']);
         
            foreach ($late30DaysStat as $day => &$stat) {
                if (strtotime($student['createdDay']) <= strtotime($day)) {
                    $stat['studentNum']++;
                }

                if ($student['isLearned'] && $student['finishedTime'] > 0 && (strtotime($student['finishedDay']) <= strtotime($day))) {
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
    }

    private function countNotesData($notes, &$late30DaysStat)
    {
        foreach ($notes as $note) {
            $note['createdDay'] = date('Y-m-d', $note['createdTime']);
         
            foreach ($late30DaysStat as $day => &$stat) {
                if (strtotime($note['createdDay']) <= strtotime($day)) {
                    $stat['noteNum']++;
                }
            }
        }
    }

    private function countAsksData($asks, &$late30DaysStat)
    {
        foreach ($asks as $ask) {
            $ask['createdDay'] = date('Y-m-d', $ask['createdTime']);
         
            foreach ($late30DaysStat as $day => &$stat) {
                if (strtotime($ask['createdDay']) <= strtotime($day)) {
                    $stat['askNum']++;
                }
            }
        }
    }

    private function countDiscussionsData($discussions, &$late30DaysStat)
    {
        foreach ($discussions as $discussion) {
            $discussion['createdDay'] = date('Y-m-d', $discussion['createdTime']);
         
            foreach ($late30DaysStat as $day => &$stat) {
                if (strtotime($discussion['createdDay']) <= strtotime($day)) {
                    $stat['discussionNum']++;
                }
            }
        }
    }

    /**
     * [getLateMonthData 获取最近一个月的数据]
     */
    private function getLateMonthData($courseId, $now)
    {
        $startTimeGreaterThan = strtotime('- 29 days', $now);
        $role = 'student';
        $result = array();
        $result['students'] = $this->getCourseService()->searchMembers(
            array(
                'courseId' => $courseId,
                'role' => $role,
                'startTimeGreaterThan' => $startTimeGreaterThan
            ),
            array('createdTime', 'ASC'),
            0,
            PHP_INT_MAX
        );

        $result['notes'] = $this->getCourseNoteService()->searchNotes(
            array(
                'courseId' => $courseId,
                'startTimeGreaterThan' => $startTimeGreaterThan
            ),
            array('createdTime' => 'ASC'),
            0,
            PHP_INT_MAX
        );

        $result['asks'] = $this->getThreadService()->searchThreads(
            array(
                'courseId' => $courseId,
                'type' => 'question',
                'startTimeGreaterThan' => $startTimeGreaterThan
            ),
            array(),
            0,
            PHP_INT_MAX
        );

        $result['discussions'] = $this->getThreadService()->searchThreads(
            array(
                'courseId' => $courseId,
                'type' => 'discussion',
                'startTimeGreaterThan' => $startTimeGreaterThan
            ),
            array(),
            0,
            PHP_INT_MAX
        );

        return $result;
    }

    protected function getCourseNoteService()
    {
        return $this->createService('Course.NoteService');
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
