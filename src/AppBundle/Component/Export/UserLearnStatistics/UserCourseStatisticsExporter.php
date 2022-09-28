<?php

namespace AppBundle\Component\Export\UserLearnStatistics;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Component\Export\Exporter;
use Biz\Classroom\Service\ClassroomService;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\Course\Service\MemberService;
use Biz\Visualization\Service\ActivityLearnDataService;

class UserCourseStatisticsExporter extends Exporter
{
    public function getTitles()
    {
        return [
            'user.learn.statistics.student_nickname',
            'user.learn.statistics.mobile',
            'classroom.name',
            'course.name',
            'user.learn.statistics.course',
            'user.learn.statistics.sum_learn_time',
            'user.learn.statistics.task_num',
            'user.learn.statistics.finish_task_num',
            'user.learn.statistics.finish_rate',
        ];
    }

    public function getContent($start, $limit)
    {
        $users = $this->getUserService()->searchUsers(
            [ArrayToolkit::parts($this->conditions, ['userIds', 'destroyed']),'isStudent' => 0],
            ['id' => 'DESC'],
            $start,
            $limit,
            ['id', 'verifiedMobile', 'nickname']
        );

        $userIds = ArrayToolkit::column($users, 'id');
        $courseMembers = $this->getCourseMemberService()->searchMembers(
            ['userIds' => $userIds, 'role' => 'student'],
            [],
            0,
            PHP_INT_MAX,
            ['courseId', 'userId', 'role', 'learnedCompulsoryTaskNum', 'courseSetId', 'classroomId']
        );

        $courseMemberData = $this->findCourseMemberData($courseMembers);

        $statisticsContent = $this->handleStatistics($users, $courseMemberData);

        return $statisticsContent;
    }

    protected function handleStatistics($users, $courseMemberData)
    {
        $statisticsContent = [];
        foreach ($users as $user) {
            $nickname = is_numeric($user['nickname']) ? $user['nickname']."\t" : $user['nickname'];
            $userData = empty($courseMemberData[$user['id']]) ? [] : $courseMemberData[$user['id']];
            foreach ($userData as $data) {
                $member = [];
                $member[] = $nickname;
                $member[] = empty($user['verifiedMobile']) ? '--' : $user['verifiedMobile']."\t";
                $member[] = $data['classroomName'];
                $member[] = $data['courseSetName'];
                $member[] = $data['courseName'];
                $member[] = $data['sumTime'];
                $member[] = $data['taskNum'];
                $member[] = $data['finishTaskNum'];
                $member[] = $data['finishRate'].'%';
                $statisticsContent[] = $member;
            }
        }

        return $statisticsContent;
    }

    protected function findCourseMemberData($courseMembers)
    {
        $courseSets = $this->getCourseSetService()->findCourseSetsByIds(array_values(array_unique(ArrayToolkit::column($courseMembers, 'courseSetId'))));
        $classrooms = $this->getClassroomService()->findClassroomsByIds(array_values(array_unique(ArrayToolkit::column($courseMembers, 'classroomId'))));
        $courses = $this->getCourseService()->findCoursesByCourseSetIds(ArrayToolkit::column($courseSets, 'id'));
        $courses = ArrayToolkit::index($courses, 'id');
        $courseMemberData = [];
        $courseMembers = ArrayToolkit::group($courseMembers, 'userId');
        foreach ($courseMembers as $userId => $members) {
            $conditions = array_merge($this->conditions, ['userId' => $userId]);
            $statistics = $this->getActivityLearnDataService()->searchCoursePlanLearnDailyData(
                $conditions,
                [],
                0,
                PHP_INT_MAX,
                ['userId', 'courseId', 'courseSetId', 'sumTime']
            );
            $statistics = ArrayToolkit::group($statistics, 'courseId');
            foreach ($members as $member) {
                if (empty($courses[$member['courseId']]) || empty($courseSets[$member['courseSetId']])) {
                    continue;
                }

                $course = $courses[$member['courseId']];
                $courseMemberData[$member['userId'].'-'.$member['courseId']] = [
                    'userId' => $member['userId'],
                    'classroomName' => empty($classrooms[$member['classroomId']]) ? '' : $classrooms[$member['classroomId']]['title'],
                    'courseSetName' => $courseSets[$member['courseSetId']]['title'],
                    'courseName' => empty($course['title']) ? $courseSets[$member['courseSetId']]['title'] : $course['title'],
                    'sumTime' => empty($statistics[$member['courseId']]) ? 0 : round(array_sum(ArrayToolkit::column($statistics[$member['courseId']], 'sumTime')) / 60, 1),
                    'taskNum' => $course['compulsoryTaskNum'],
                    'finishTaskNum' => $member['learnedCompulsoryTaskNum'],
                    'finishRate' => empty($course['compulsoryTaskNum']) ? 1 : round($member['learnedCompulsoryTaskNum'] / $course['compulsoryTaskNum'], 2) * 100,
                ];
            }
        }

        return ArrayToolkit::group($courseMemberData, 'userId');
    }

    public function canExport()
    {
        $user = $this->getUser();

        return $user->isAdmin();
    }

    public function getCount()
    {
        return $this->getUserService()->countUsers(ArrayToolkit::parts($this->conditions, ['userIds', 'destroyed']));
    }

    public function buildCondition($conditions)
    {
        $conditions['userIds'] = [];
        if (!empty($conditions['keyword'])) {
            $userConditions = ['nickname' => $conditions['keyword']];
            if ('mobile' == $conditions['keywordType']) {
                unset($userConditions['nickname']);
                $userConditions['verifiedMobile'] = $conditions['keyword'];
            }
            $users = $this->getUserService()->searchUsers(
                $userConditions,
                [],
                0,
                PHP_INT_MAX,
                ['id']
            );
            $conditions['userIds'] = ArrayToolkit::column($users, 'id');
            unset($conditions['keywordType']);
            unset($conditions['keyword']);
        }

        $conditions['destroyed'] = 0;

        return $conditions;
    }

    protected function getPageConditions()
    {
        return [$this->parameter['start'], 50];
    }

    /**
     * @return ActivityLearnDataService
     */
    protected function getActivityLearnDataService()
    {
        return $this->getBiz()->service('Visualization:ActivityLearnDataService');
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->getBiz()->service('Course:CourseSetService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->getBiz()->service('Course:CourseService');
    }

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->getBiz()->service('Classroom:ClassroomService');
    }

    /**
     * @return MemberService
     */
    protected function getCourseMemberService()
    {
        return $this->getBiz()->service('Course:MemberService');
    }
}
