<?php

namespace Biz\Course\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use Biz\Course\Dao\ReportDao;
use Biz\Task\Service\TaskService;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\MemberService;
use Biz\Course\Service\ReportService;
use Biz\Course\Service\ThreadService;
use Biz\Task\Service\TaskResultService;
use Biz\Course\Service\CourseNoteService;
use Biz\Task\Service\TryViewLogService;
use Biz\Testpaper\Service\TestpaperService;
use Biz\User\Service\UserService;
use AppBundle\Common\SimpleValidator;

class ReportServiceImpl extends BaseService implements ReportService
{
    public function summary($courseId)
    {
        $course = $this->getCourseService()->getCourse($courseId);
        $defaultSummary = array(
            'studentNum' => 0,
            'studentNumToday' => 0,
            'finishedNum' => 0,
            'finishedNumToday' => 0,
            'tryViewNum' => 0,
            'tryViewNumToday' => 0,
            'noteNum' => 0,
            'noteNumToday' => 0,
            'askNum' => 0,
            'askNumToday' => 0,
            'discussionNum' => 0,
            'discussionNumToday' => 0,
        );

        $summary = array();

        $startTime = strtotime(date('Y-m-d'));

        $summary['studentNum'] = $this->getCourseMemberService()->countMembers(array('courseId' => $courseId, 'role' => 'student'));
        $summary['studentNumToday'] = $this->getCourseMemberService()->countMembers(array('courseId' => $courseId, 'role' => 'student', 'startTimeGreaterThan' => $startTime));
        $summary['finishedNum'] = $this->getCourseMemberService()->countMembers(array(
            'role' => 'student',
            'learnedCompulsoryTaskNumGreaterThan' => $course['compulsoryTaskNum'],
            'courseId' => $courseId,
        ));
        $summary['finishedNumToday'] = $this->getCourseMemberService()->countMembers(array(
            'role' => 'student',
            'learnedCompulsoryTaskNumGreaterThan' => $course['compulsoryTaskNum'],
            'courseId' => $courseId,
            'lastLearnTimeGreaterThan' => $startTime,
        ));
        $summary['tryViewNum'] = $this->getTaskTryViewService()->countTryViewLogs(array('courseId' => $courseId));
        $summary['tryViewNumToday'] = $this->getTaskTryViewService()->countTryViewLogs(array('courseId' => $courseId, 'createdTime_GE' => $startTime));
        $summary['noteNum'] = $this->getCourseNoteService()->countCourseNotes(array('courseId' => $courseId));
        $summary['noteNumToday'] = $this->getCourseNoteService()->countCourseNotes(array('courseId' => $courseId, 'startTimeGreaterThan' => $startTime));
        $summary['askNum'] = $this->getThreadService()->countThreads(array('courseId' => $courseId, 'type' => 'question'));
        $summary['askNumToday'] = $this->getThreadService()->countThreads(array('courseId' => $courseId, 'type' => 'question', 'startCreatedTime' => $startTime));
        $summary['discussionNum'] = $this->getThreadService()->countThreads(array('courseId' => $courseId, 'type' => 'discussion'));
        $summary['discussionNumToday'] = $this->getThreadService()->countThreads(array('courseId' => $courseId, 'type' => 'discussion', 'startCreatedTime' => $startTime));

        $summary = array_merge($defaultSummary, $summary);

        return $summary;
    }

    public function getCompletionRateTrend($courseId, $startDate, $endDate)
    {
        $course = $this->getCourseService()->getCourse($courseId);

        $historyData = $this->getReportDao()->findCompleteCourseCountGroupByDate($courseId, 0, strtotime('-1 day', strtotime($startDate)));

        $userPickData = $this->getReportDao()->findCompleteCourseCountGroupByDate($courseId, strtotime($startDate), strtotime('+1 day', strtotime($endDate) - 1));

        $total = 0;
        foreach ($historyData as $singleData) {
            $total += $singleData['count'];
        }

        $end = new \DateTime($endDate);
        $end->modify('+1 day');
        $period = new \DatePeriod(
            new \DateTime($startDate),
            new \DateInterval('P1D'),
            $end
        );

        $userPickData = ArrayToolkit::index($userPickData, 'date');

        $result = array();
        foreach ($period as $date) {
            $dateStr = $date->format('Y-m-d');
            if (isset($userPickData[$dateStr])) {
                $total += $userPickData[$dateStr]['count'];
            }

            $result[] = array(
                'date' => $dateStr,
                'finishedNum' => $total,
                'finishedRate' => $this->getPercent($total, $course['studentNum']),
            );
        }

        return $result;
    }

    public function getStudentTrend($courseId, $timeRange)
    {
        $studentIncreaseData = $this->getCourseMemberService()->findDailyIncreaseNumByCourseIdAndRoleAndTimeRange($courseId, 'student', $timeRange);
        $tryViewIncreaseData = $this->getTaskTryViewService()->searchLogCountsByCourseIdAndTimeRange($courseId, $timeRange);
        $end = new \DateTime($timeRange['endDate']);
        $end->modify('+1 day');
        $period = new \DatePeriod(
            new \DateTime($timeRange['startDate']),
            new \DateInterval('P1D'),
            $end
        );

        $studentIncreaseData = ArrayToolkit::index($studentIncreaseData, 'date');
        $tryViewIncreaseData = ArrayToolkit::index($tryViewIncreaseData, 'date');

        $result = array();
        foreach ($period as $date) {
            $dateStr = $date->format('Y-m-d');
            $studentIncreaseNum = isset($studentIncreaseData[$dateStr]) ? $studentIncreaseData[$dateStr]['count'] : 0;
            $tryViewIncreaseNum = isset($tryViewIncreaseData[$dateStr]) ? $tryViewIncreaseData[$dateStr]['count'] : 0;
            $result[] = array(
                'date' => $dateStr,
                'studentIncrease' => $studentIncreaseNum,
                'tryViewIncrease' => $tryViewIncreaseNum,
            );
        }

        return $result;
    }

    public function getStudentDetail($courseId, $userIds, $taskLimit = 20)
    {
        $users = $this->getUserService()->searchUsers(array('userIds' => $userIds), array(), 0, count($userIds));
        $users = ArrayToolkit::index($users, 'id');

        $courseTasks = $this->getTaskService()->searchTasks(
            array(
                'courseId' => $courseId,
                'isOptional' => 0,
                'status' => 'published',
            ),
            array('seq' => 'ASC'),
            0,
            $taskLimit
        );
        $taskIds = ArrayToolkit::column($courseTasks, 'id');

        $taskResults = $this->getTaskResultService()->searchTaskResults(
            array(
                'courseId' => $courseId,
                'userIds' => $userIds,
                'courseTaskIds' => $taskIds,
            ),
            array(),
            0,
            PHP_INT_MAX
        );

        $taskResults = ArrayToolkit::groupIndex($taskResults, 'userId', 'courseTaskId');

        return array($users, $courseTasks, $taskResults);
    }

    public function buildStudentDetailOrderBy($conditions)
    {
        $orderBy = array('createdTime' => 'DESC');
        if (!empty($conditions['orderBy'])) {
            switch ($conditions['orderBy']) {
                case 'createdTimeDesc':
                    $orderBy = array('createdTime' => 'DESC');
                    break;
                case 'createdTimeAsc':
                    $orderBy = array('createdTime' => 'ASC');
                    break;
                case 'learnedCompulsoryTaskNumDesc':
                    $orderBy = array('learnedCompulsoryTaskNum' => 'DESC');
                    break;
                case 'learnedCompulsoryTaskNumAsc':
                    $orderBy = array('learnedCompulsoryTaskNum' => 'ASC');
                    break;
            }
        }

        return $orderBy;
    }

    public function buildStudentDetailConditions($conditions, $courseId)
    {
        $course = $this->getCourseService()->getCourse($courseId);
        $memberConditions = array(
            'courseId' => $course['id'],
            'role' => 'student',
        );

        if (!empty($conditions['range'])) {
            switch ($conditions['range']) {
                case 'unLearnedSevenDays':
                    $endTime = strtotime(date('Y-m-d', strtotime('-7 days')));
                    $memberConditions['lastLearnTimeLessThen'] = $endTime;
                    $memberConditions['learnedCompulsoryTaskNumLT'] = $course['compulsoryTaskNum'];
                    break;
                case 'unFinished':
                    $memberConditions['learnedCompulsoryTaskNumLT'] = $course['compulsoryTaskNum'];
                    break;
            }
        }

        if (!empty($conditions['nameOrMobile'])) {
            $mobile = SimpleValidator::mobile($conditions['nameOrMobile']);
            if ($mobile) {
                $user = $this->getUserService()->getUserByVerifiedMobile($conditions['nameOrMobile']);
                $users = empty($user) ? array() : array($user);
            } else {
                $users = $this->getUserService()->searchUsers(
                    array('nickname' => $conditions['nameOrMobile']),
                    array(),
                    0,
                    PHP_INT_MAX
                );
            }

            if (empty($users)) {
                $memberConditions['userId'] = 0;
            } else {
                $userIds = ArrayToolkit::column($users, 'id');
                $memberConditions['userIds'] = $userIds;
            }
        }

        return $memberConditions;
    }

    public function searchUserIdsByCourseIdAndFilterAndSortAndKeyword($courseId, $filter, $sort, $start, $limit)
    {
        $conditions = $this->prepareCourseIdAndFilter($courseId, $filter);
        $orderBy = $this->prepareSort($sort);
        $userIds = $this->getCourseMemberService()->searchMemberIds($conditions, $orderBy, $start, $limit);

        return $userIds;
    }

    public function getLateMonthLearnData($courseId)
    {
        $now = time();
        $lastMonthData = $this->getLatestMonthData($courseId, $now);
        $before30DaysData = $this->getAMonthAgoStatCount($courseId, $now);
        $late30DaysStat = array();
        for ($i = 29; $i >= 0; --$i) {
            $day = date('Y-m-d', strtotime('-'.$i.' days'));
            $late30DaysStat[$day]['day'] = date('m-d', strtotime('-'.$i.' days'));
            $late30DaysStat[$day]['studentNum'] = $before30DaysData['studentNum'];
            $late30DaysStat[$day]['finishedNum'] = $before30DaysData['finishedNum'];
            $late30DaysStat[$day]['finishedRate'] = $before30DaysData['finishedRate'];
            $late30DaysStat[$day]['noteNum'] = $before30DaysData['noteNum'];
            $late30DaysStat[$day]['askNum'] = $before30DaysData['askNum'];
            $late30DaysStat[$day]['discussionNum'] = $before30DaysData['discussionNum'];
        }

        //隐藏笔记、提问、讨论的历史数据
        $this->countStudentsData($courseId, $lastMonthData['students'], $late30DaysStat);

        return $late30DaysStat;
    }

    public function getCourseTaskLearnData($tasks, $courseId)
    {
        if (empty($tasks)) {
            return array();
        }

        $course = $this->getCourseService()->getCourse($courseId);
        $studentNum = $course['studentNum'];
        foreach ($tasks as &$task) {
            if ($task['status'] !== 'published') {
                $task['finishedNum'] = $task['learnNum'] = $task['notStartedNum'] = $task['rate'] = 0;

                continue;
            }

            $task['finishedNum'] = $this->getTaskResultService()->countUsersByTaskIdAndLearnStatus($task['id'], 'finish');
            $task['learnNum'] = $this->getTaskResultService()->countUsersByTaskIdAndLearnStatus($task['id'], 'start');
            $task['notStartedNum'] = $studentNum - $task['finishedNum'] - $task['learnNum'];
            $task['rate'] = $this->getPercent($task['finishedNum'], $studentNum);
        }

        return $tasks;
    }

    private function countMembersFinishedAllTasksByCourseId($courseId, $finishedTimeLessThan = '')
    {
        $course = $this->getCourseService()->getCourse($courseId);
        $condition = array(
            'role' => 'student',
            'learnedCompulsoryTaskNumGreaterThan' => $course['compulsoryTaskNum'],
            'courseId' => $courseId,
        );

        if (!empty($finishedTimeLessThan)) {
            $condition['lastLearnTime_LE'] = $finishedTimeLessThan;
        }
        $memberCount = $this->getCourseMemberService()->countMembers($condition);

        return $memberCount;
    }

    private function countStudentsData($courseId, $students, &$late30DaysStat)
    {
        $course = $this->getCourseService()->getCourse($courseId);
        foreach ($students as $student) {
            $student['createdDay'] = date('Y-m-d', $student['createdTime']);
            $student['finishedDay'] = date('Y-m-d', $student['lastLearnTime']);

            foreach ($late30DaysStat as $day => &$stat) {
                if (strtotime($student['createdDay']) <= strtotime($day)) {
                    ++$stat['studentNum'];
                }

                if ($student['learnedCompulsoryTaskNum'] >= $course['compulsoryTaskNum'] && ($student['lastLearnTime'] <= strtotime($day))) {
                    ++$stat['finishedNum'];
                }
            }
        }

        foreach ($late30DaysStat as $day => &$stat) {
            $stat['finishedRate'] = $this->getPercent($stat['finishedNum'], $stat['studentNum']);
        }
    }

    private function countNotesData($notes, &$late30DaysStat)
    {
        foreach ($notes as $note) {
            $note['createdDay'] = date('Y-m-d', $note['createdTime']);

            foreach ($late30DaysStat as $day => &$stat) {
                if (strtotime($note['createdDay']) <= strtotime($day)) {
                    ++$stat['noteNum'];
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
                    ++$stat['askNum'];
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
                    ++$stat['discussionNum'];
                }
            }
        }
    }

    private function getPercent($count, $total)
    {
        $percent = $total == 0 ? 0 : round($count * 100 / $total, 3);

        return $percent > 100 ? 100 : $percent;
    }

    /**
     * @return CourseNoteService
     */
    protected function getCourseNoteService()
    {
        return $this->createService('Course:CourseNoteService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return MemberService
     */
    protected function getCourseMemberService()
    {
        return $this->createService('Course:MemberService');
    }

    /**
     * @return ThreadService
     */
    protected function getThreadService()
    {
        return $this->createService('Course:ThreadService');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->createService('Task:TaskService');
    }

    /**
     * @return TaskResultService
     */
    protected function getTaskResultService()
    {
        return $this->createService('Task:TaskResultService');
    }

    /**
     * @return TryViewLogService
     */
    protected function getTaskTryViewService()
    {
        return $this->createService('Task:TryViewLogService');
    }

    /**
     * @return TestpaperService
     */
    protected function getTestpaperService()
    {
        return $this->createService('Testpaper:TestpaperService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    /**
     * @return ReportDao
     */
    protected function getReportDao()
    {
        return $this->createDao('Course:ReportDao');
    }
}
