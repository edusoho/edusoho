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
            'user.learn.statistics.nickname',
            'user.learn.statistics.mobile',
            'classroom.name',
            'course.name',
            'user.learn.statistics.course',
            'user.learn.statistics.sum_learn_time',
            'user.learn.statistics.pure_learn_time',
            'user.learn.statistics.task_num',
            'user.learn.statistics.finish_task_num',
            'user.learn.statistics.finish_rate',
        ];
    }

    public function getContent($start, $limit)
    {
        $users = $this->getUserService()->searchUsers(
            ArrayToolkit::parts($this->conditions, ['userIds', 'destroyed']),
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
            ['courseId', 'userId', 'role', 'learnedNum', 'courseSetId', 'classroomId']
        );

        $courseMemberData = $this->findCourseMemberData($courseMembers);

        $statisticsContent = $this->handleStatistics($users, $courseMemberData);

        return $statisticsContent;
    }

    protected function handleStatistics($users, $courseMemberData)
    {
        $statisticsContent = [];
        foreach ($users as $user) {
            $nickname = is_numeric($user['nickname']) ? $user['nickname']."\t" : $user['nickname'];;
            $mobile = $user['verifiedMobile'];
            $userData = empty($courseMemberData[$user['id']]) ? [] : $courseMemberData[$user['id']];
            foreach ($userData as $data) {
                $member = [];
                $member[] = $nickname;
                $member[] = $mobile;
                $member[] = $data['classroomName'];
                $member[] = $data['courseSetName'];
                $member[] = $data['courseName'];
                $member[] = $data['sumTime'];
                $member[] = $data['pureTime'];
                $member[] = $data['taskNum'];
                $member[] = $data['finishTaskNum'];
                $member[] = $data['finishRate'].'%';
                $nickname = $mobile = ' ';
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
                ['userId', 'courseId', 'courseSetId', 'sumTime', 'pureTime']
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
                    'sumTime' => empty($statistics[$member['courseId']]) ? 0 : round(array_sum(ArrayToolkit::column($statistics[$member['courseId']], 'sumTime')) / 60),
                    'pureTime' => empty($statistics[$member['courseId']]) ? 0 : round(array_sum(ArrayToolkit::column($statistics[$member['courseId']], 'pureTime')) / 60),
                    'taskNum' => $course['taskNum'],
                    'finishTaskNum' => $member['learnedNum'],
                    'finishRate' => empty($course['taskNum']) ? 0 : round($member['learnedNum'] / $course['taskNum'], 2) * 100,
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
        if (!empty($conditions['nickname'])) {
            $users = $this->getUserService()->searchUsers(
                ['nickname' => $conditions['nickname']],
                [],
                0,
                PHP_INT_MAX
            );

            $conditions['userIds'] = ArrayToolkit::column($users, 'id');
            unset($conditions['nickname']);
        } else {
            $conditions['userIds'] = [];
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
