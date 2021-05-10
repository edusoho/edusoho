<?php

namespace Biz\Classroom\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\SimpleValidator;
use Biz\BaseService;
use Biz\Classroom\Dao\ClassroomCourseDao;
use Biz\Classroom\DateTimeRange;
use Biz\Classroom\Service\ClassroomService;
use Biz\Classroom\Service\MemberService;
use Biz\Classroom\Service\ReportService;
use Biz\Common\CommonException;
use Biz\Course\Service\CourseService;
use Biz\User\Service\UserService;
use Biz\Visualization\Service\CoursePlanLearnDataDailyStatisticsService;

class ReportServiceImpl extends BaseService implements ReportService
{
    public function getStudentTrend($classroomId, DateTimeRange $timeRange)
    {
        $studentsIncreaseData = ArrayToolkit::index($this->getClassroomMemberService()->findDailyIncreaseDataByClassroomIdAndRoleWithTimeRange(
            $classroomId,
            'student',
            $timeRange->getStartTime(),
            $timeRange->getEndDateTime()->modify('+1 day')->getTimestamp()
        ), 'date');

        $auditorsIncreaseData = ArrayToolkit::index($this->getClassroomMemberService()->findDailyIncreaseDataByClassroomIdAndRoleWithTimeRange(
            $classroomId,
            'auditor',
            $timeRange->getStartTime(),
            $timeRange->getEndDateTime()->modify('+1 day')->getTimestamp()
        ), 'date');

        $period = new \DatePeriod(
            $timeRange->getStartDateTime(),
            new \DateInterval('P1D'),
            $timeRange->getEndDateTime()->modify('+1 day')
        );

        $results = [];
        foreach ($period as $date) {
            $dateStr = $date->format('Y-m-d');
            $studentIncreaseNum = isset($studentsIncreaseData[$dateStr]) ? $studentsIncreaseData[$dateStr]['count'] : 0;
            $auditorIncreaseNum = isset($auditorsIncreaseData[$dateStr]) ? $auditorsIncreaseData[$dateStr]['count'] : 0;
            $results[] = [
                'date' => $dateStr,
                'studentIncrease' => $studentIncreaseNum,
                'auditorIncrease' => $auditorIncreaseNum,
            ];
        }

        return $results;
    }

    public function getStudentDetailList($classroomId, $filterConditions, $sort, $start, $limit)
    {
//        $this->getClassroomService()->tryManageClassroom($classroomId);
        $classroom = $this->getClassroomService()->getClassroom($classroomId);
        $conditions = $this->prepareStudentDetailFilterConditions($filterConditions, $classroom);
        $conditions = array_merge($conditions, [
            'classroomId' => $classroomId,
            'role' => 'student',
        ]);
        $orderBy = $this->prepareStudentDetailSort($sort);
        $members = $this->getClassroomService()->searchMembers($conditions, $orderBy, $start, $limit);
        $userIds = ArrayToolkit::column($members, 'userId');
        $groupCourseMembers = ArrayToolkit::groupIndex($this->getCourseMemberService()->findCourseMembersByUserIdsAndClassroomId($userIds, $classroomId), 'userId', 'courseId');
        $classroomCourses = $this->getClassroomService()->findCoursesByClassroomId($classroomId);
        $courseIds = ArrayToolkit::column($classroomCourses, 'id');

        foreach ($members as &$member) {
            $member['courseMembers'] = empty($groupCourseMembers[$member['userId']]) ? [] : $groupCourseMembers[$member['userId']];
            $member['rate'] = empty($classroom['compulsoryTaskNum']) ? 0 : $this->getPercent($member['learnedCompulsoryTaskNum'], $classroom['compulsoryTaskNum']);
            $member['learnedTime'] = $this->getCoursePlanLearnDataDailyStatisticsService()->sumLearnedTimeByConditions(['courseIds' => $courseIds, 'userId' => $member['userId']]);
        }

        return $members;
    }

    public function getStudentDetail($classroomId, $userId)
    {
//        $this->getClassroomService()->tryManageClassroom($classroomId);
        $member = $this->getClassroomService()->getClassroomMember($classroomId, $userId);
        $courseMembers = ArrayToolkit::index($this->getCourseMemberService()->findCourseMembersByUserIdAndClassroomId($userId, $classroomId), 'courseId');
        $member['courseMembers'] = $courseMembers;

        return $member;
    }

    public function getStudentDetailCount($classroomId, $filterConditions)
    {
//        $this->getClassroomService()->tryManageClassroom($classroomId);
        $classroom = $this->getClassroomService()->getClassroom($classroomId);
        $conditions = $this->prepareStudentDetailFilterConditions($filterConditions, $classroom);
        $conditions = array_merge($conditions, [
            'classroomId' => $classroomId,
            'role' => 'student',
        ]);

        return $this->getClassroomService()->searchMemberCount($conditions);
    }

    public function getCourseDetailList($classroomId, $filterConditions, $start, $limit)
    {
        $classroom = $this->getClassroomService()->getClassroom($classroomId);
        if (!empty($filterConditions['nameLike'])) {
            $courses = ArrayToolkit::index($this->getCourseService()->searchCourses([
                'courseSetTitleLike' => $filterConditions['nameLike'],
            ], ['id' => 'DESC'], 0, PHP_INT_MAX), 'id');
            $courseIds = ArrayToolkit::column($courses, 'id');
            $classroomCourses = $this->getClassroomCourseDao()->search(['courseIds' => $courseIds, 'classroomId' => $classroom['id']], ['seq' => 'ASC'], 0, PHP_INT_MAX);
        } else {
            $classroomCourses = $this->getClassroomCourseDao()->search(['classroomId' => $classroom['id']], ['seq' => 'ASC'], 0, PHP_INT_MAX);
            $courseIds = ArrayToolkit::column($classroomCourses, 'courseId');
            $courses = ArrayToolkit::index($this->getCourseService()->findCoursesByIds($courseIds), 'id');
        }

        $courseList = [];

        foreach ($classroomCourses as $classroomCourse) {
            $course = empty($courses[$classroomCourse['courseId']]) ? [] : $courses[$classroomCourse['courseId']];
            if (empty($course)) {
                $course['finishedNum'] = $course['learnNum'] = $course['notStartedNum'] = $course['rate'] = 0;
                continue;
            }
            $course['finishedNum'] = $this->getCourseMemberService()->countMembers(['courseId' => $course['id'], 'role' => 'student', 'isLearned' => 1]);
            $course['learnNum'] = $this->getCourseMemberService()->countMembers(['courseId' => $course['id'],  'role' => 'student', 'startLearnTime_GT' => '0', 'isLearned' => '0']);
            $members = $this->getCourseMemberService()->searchMembers(['courseId' => $course['id'], 'role' => 'student'], [], 0, PHP_INT_MAX, ['learnedCompulsoryTaskNum']);
            $finishedTasksNum = empty($members) ? 0 : array_sum(ArrayToolkit::column($members, 'learnedCompulsoryTaskNum'));
            $course['notStartedNum'] = $course['studentNum'] - $course['finishedNum'] - $course['learnNum'];
            $course['rate'] = $this->getPercent($finishedTasksNum, $course['studentNum'] * $course['compulsoryTaskNum']);
            $courseList[] = $course;
        }

        if (empty($filterConditions['orderBy'])) {
            return array_slice($courseList, $start, $limit);
        }

        if ('CompletionRateDesc' === $filterConditions['orderBy']) {
            $ascending = false;
        } else {
            $ascending = true;
        }
        $courseList = ArrayToolkit::sortPerArrayValue($courseList, 'rate', $ascending);

        return array_slice($courseList, $start, $limit);
    }

    public function getCourseDetailCount($classroomId, $filterConditions)
    {
        $classroom = $this->getClassroomService()->getClassroom($classroomId);
        if (!empty($filterConditions['nameLike'])) {
            $courses = $this->getCourseService()->searchCourses([
                'courseSetTitleLike' => $filterConditions['nameLike'],
            ], ['id' => 'DESC'], 0, PHP_INT_MAX);
            $courseIds = ArrayToolkit::column($courses, 'id');
            $classroomCoursesCount = $this->getClassroomCourseDao()->count(['courseIds' => $courseIds, 'classroomId' => $classroom['id']]);
        } else {
            $classroomCoursesCount = $this->getClassroomCourseDao()->count(['classroomId' => $classroom['id']]);
        }

        return $classroomCoursesCount;
    }

    public function getCourseLearnDetail($classroomId, $courseId, $filterConditions, $start, $limit)
    {
//        $this->getClassroomService()->tryManageClassroom($classroomId, null);
        $classroom = $this->getClassroomService()->getClassroom($classroomId);
        if (empty($classroom)) {
            return [];
        }
        $course = $this->getCourseService()->getCourse($courseId);
        if (empty($course)) {
            return [];
        }
        $conditions = array_merge($this->prepareCourseLearnDetailFilterConditions($filterConditions), ['courseId' => $courseId, 'role' => 'student']);
        $courseMembers = $this->getCourseMemberService()->searchMembers($conditions, ['id' => 'ASC'], $start, $limit);
        foreach ($courseMembers as &$courseMember) {
            $courseMember['finishRate'] = $this->getPercent($courseMember['learnedCompulsoryTaskNum'], $course['compulsoryTaskNum']);
            if (empty($courseMember['lastLearnTime'])) {
                $courseMember['learnStatus'] = 'unLearn';
            } elseif (empty($courseMember['isLearned'])) {
                $courseMember['learnStatus'] = 'learning';
            } else {
                $courseMember['learnStatus'] = 'learned';
            }
        }

        return $courseMembers;
    }

    public function getCourseLearnDetailCount($classroomId, $courseId, $filterConditions)
    {
//        $this->getClassroomService()->tryManageClassroom($classroomId, null);
        $classroom = $this->getClassroomService()->getClassroom($classroomId);
        if (empty($classroom)) {
            return 0;
        }
        $course = $this->getCourseService()->getCourse($courseId);
        if (empty($course)) {
            return 0;
        }
        $conditions = array_merge($this->prepareCourseLearnDetailFilterConditions($filterConditions), ['courseId' => $courseId, 'role' => 'student']);

        return $this->getCourseMemberService()->countMembers($conditions) ?: 0;
    }

    protected function prepareCourseLearnDetailFilterConditions($filterConditions)
    {
        $conditions = [];
        if (!empty($filterConditions['filter'])) {
            switch ($filterConditions['filter']) {
                case 'unLearn':
                    $conditions['lastLearnTime'] = '0';
                    $conditions['isLearned'] = '0';
                    break;
                case 'learning':
                    $conditions['lastLearnTime_GT'] = '0';
                    $conditions['isLearned'] = '0';
                    break;
                case 'learned':
                    $conditions['isLearned'] = '1';
                    $conditions['finishedTime_GT'] = '0';
                    break;
            }
        }

        return $conditions;
    }

    /**
     * @param $filterConditions array
     * [
     *      'filter' => 'all',//all: 全部，unFinished: 未完成,sevenDaysUnLearn: 七日未学
     *      'nicknameOrMobileLike' => 'abc',
     * ]
     * @param $classroom
     *
     * @return array
     */
    protected function prepareStudentDetailFilterConditions($filterConditions, $classroom)
    {
        $conditions = [];
        if (!empty($filterConditions['nameOrMobile'])) {
            $mobile = SimpleValidator::mobile($filterConditions['nameOrMobile']);
            if ($mobile) {
                $user = $this->getUserService()->getUserByVerifiedMobile($filterConditions['nameOrMobile']);
                $users = empty($user) ? [] : [$user];
            } else {
                $users = $this->getUserService()->searchUsers(
                    ['nickname' => $filterConditions['nameOrMobile']],
                    [],
                    0,
                    PHP_INT_MAX
                );
            }

            if (empty($users)) {
                $conditions['userId'] = -1;
            } else {
                $userIds = ArrayToolkit::column($users, 'id');
                $conditions['userIds'] = $userIds;
            }
        }

        if (!empty($filterConditions['filter'])) {
            switch ($filterConditions['filter']) {
                case 'unLearnedSevenDays':
                    $startTime = strtotime(date('Y-m-d', strtotime('-7 days')));
                    $conditions['lastLearnTime_LT'] = $startTime;
                    $conditions['learnedCompulsoryTaskNum_LT'] = $classroom['compulsoryTaskNum'];
                    break;
                case 'unFinished':
                    $conditions['learnedCompulsoryTaskNum_LT'] = $classroom['compulsoryTaskNum'];
                    break;
            }
        }

        return $conditions;
    }

    /**
     * @param $sort String
     * joinTimeDesc: 加入时间倒序，joinTimeAsc: 加入时间正序，CompletionRateDesc: 完成率倒序，CompletionRateAsc: 完成率正序
     *
     * @return array
     *
     * @throws \Exception
     */
    protected function prepareStudentDetailSort($sort)
    {
        $orderBy = [];
        switch ($sort) {
            case 'joinTimeDesc':
                $orderBy = ['createdTime' => 'DESC'];
                break;
            case 'joinTimeAsc':
                $orderBy = ['createdTime' => 'ASC'];
                break;
            case 'CompletionRateDesc':
                $orderBy = ['learnedCompulsoryTaskNum' => 'DESC'];
                break;
            case 'CompletionRateAsc':
                $orderBy = ['learnedCompulsoryTaskNum' => 'ASC'];
                break;
            default:
                $this->createNewException(CommonException::ERROR_PARAMETER());
        }

        return $orderBy;
    }

    private function getPercent($count, $total)
    {
        $percent = 0 === (int) $total ? 0 : round($count * 100 / $total, 3);

        return $percent > 100 ? 100 : $percent;
    }

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->biz->service('Classroom:ClassroomService');
    }

    /**
     * @return MemberService
     */
    protected function getClassroomMemberService()
    {
        return $this->biz->service('Classroom:MemberService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->biz->service('User:UserService');
    }

    /**
     * @return \Biz\Course\Service\MemberService
     */
    protected function getCourseMemberService()
    {
        return $this->biz->service('Course:MemberService');
    }

    /**
     * @return ClassroomCourseDao
     */
    protected function getClassroomCourseDao()
    {
        return $this->biz->dao('Classroom:ClassroomCourseDao');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->biz->service('Course:CourseService');
    }

    /**
     * @return CoursePlanLearnDataDailyStatisticsService
     */
    protected function getCoursePlanLearnDataDailyStatisticsService()
    {
        return $this->biz->service('Visualization:CoursePlanLearnDataDailyStatisticsService');
    }
}
