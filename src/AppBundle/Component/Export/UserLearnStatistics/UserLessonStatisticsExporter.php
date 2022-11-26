<?php

namespace AppBundle\Component\Export\UserLearnStatistics;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Component\Export\Exporter;
use Biz\Classroom\Service\ClassroomService;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\Course\Service\MemberService;
use Biz\Task\Service\TaskResultService;
use Biz\Task\Service\TaskService;
use Biz\Visualization\Service\ActivityLearnDataService;

class UserLessonStatisticsExporter extends Exporter
{
    public function getTitles()
    {
        return [
            'user.learn.statistics.student_nickname',
            'user.learn.statistics.mobile',
            'classroom.name',
            'course.name',
            'user.learn.statistics.course_name',
            'user.learn.statistics.lesson_name',
            'user.learn.statistics.lesson_type',
            'user.learn.statistics.type',
            'user.learn.statistics.video_length',
            'user.learn.statistics.sum_learn_time',
            'user.learn.statistics.pure_learn_time',
            'user.learn.statistics.lesson.finish_rate',
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
            ['courseId', 'userId', 'role', 'learnedNum', 'courseSetId', 'classroomId']
        );

        $memberTaskData = $this->findMemberTaskData($courseMembers);

        $statisticsContent = $this->handleStatistics($users, $memberTaskData);

        return $statisticsContent;
    }

    protected function handleStatistics($users, $memberTaskData)
    {
        $statisticsContent = [];
        foreach ($users as $user) {
            $nickname = is_numeric($user['nickname']) ? $user['nickname']."\t" : $user['nickname'];
            $userData = empty($memberTaskData[$user['id']]) ? [] : $memberTaskData[$user['id']];
            foreach ($userData as $data) {
                $member = [];
                $member[] = $nickname;
                $member[] = empty($user['verifiedMobile']) ? '--' : $user['verifiedMobile']."\t";
                $member[] = $data['classroomName'];
                $member[] = $data['courseSetName'];
                $member[] = $data['courseName'];
                $member[] = $data['taskName'];
                $member[] = $data['taskType'];
                $member[] = $data['type'];
                $member[] = $data['length'];
                $member[] = $data['sumTime'];
                $member[] = $data['pureTime'];
                $member[] = $data['finishStatus'];
                $nickname = ' ';
                $statisticsContent[] = $member;
            }
        }

        return $statisticsContent;
    }

    protected function findMemberTaskData($courseMembers)
    {
        $courseSets = $this->getCourseSetService()->findCourseSetsByIds(array_values(array_unique(ArrayToolkit::column($courseMembers, 'courseSetId'))));
        $classrooms = $this->getClassroomService()->findClassroomsByIds(array_values(array_unique(ArrayToolkit::column($courseMembers, 'classroomId'))));
        $courses = $this->getCourseService()->findCoursesByCourseSetIds(ArrayToolkit::column($courseSets, 'id'));
        $courses = ArrayToolkit::index($courses, 'id');
        $tasks = $this->getTaskService()->findTasksByCourseIds(ArrayToolkit::column($courses, 'id'));
        $tasks = ArrayToolkit::group($tasks, 'courseId');
        $courseMembers = ArrayToolkit::group($courseMembers, 'userId');
        $taskTypes = $this->getTaskTypes();
        $memberTaskData = [];
        foreach ($courseMembers as $userId => $members) {
            foreach ($members as $member) {
                if (empty($courses[$member['courseId']]) || empty($courseSets[$member['courseSetId']])) {
                    continue;
                }

                $course = $courses[$member['courseId']];
                $conditions = array_merge($this->conditions, ['userId' => $userId]);
                $statistics = $this->getActivityLearnDataService()->searchActivityLearnDailyData(
                    $conditions,
                    [],
                    0,
                    PHP_INT_MAX,
                    ['userId', 'activityId', 'taskId', 'courseId', 'courseSetId', 'sumTime', 'pureTime']
                );
                $statistics = ArrayToolkit::group($statistics, 'taskId');
                $taskResults = $this->getTaskResultService()->findTaskResultsByUserId($userId);
                $taskResults = ArrayToolkit::index($taskResults, 'courseTaskId');
                $courseTasks = empty($tasks[$member['courseId']]) ? [] : $tasks[$member['courseId']];
                $classroomName = empty($classrooms[$member['classroomId']]) ? '' : $classrooms[$member['classroomId']]['title'];
                $courseSetName = empty($courseSets[$member['courseSetId']]) ? '' : $courseSets[$member['courseSetId']]['title'];
                $courseName = empty($course['title']) ? $courseSetName : $course['title'];
                foreach ($courseTasks as $task) {
                    if (empty($taskTypes[$task['type']])) {
                        continue;
                    }

                    $memberTaskData[] = [
                        'userId' => $member['userId'],
                        'classroomName' => $classroomName,
                        'courseSetName' => $courseSetName,
                        'courseName' => $courseName,
                        'taskName' => $task['title'],
                        'taskType' => $this->trans($taskTypes[$task['type']]['name']),
                        'type' => $this->getTaskOptional($task['isOptional']),
                        'length' => in_array($task['type'], ['audio', 'video']) ? round($task['length'] / 60, 1) : '',
                        'sumTime' => empty($statistics[$task['id']]) ? 0 : round(array_sum(ArrayToolkit::column($statistics[$task['id']], 'sumTime')) / 60, 1),
                        'pureTime' => empty($statistics[$task['id']]) ? 0 : round(array_sum(ArrayToolkit::column($statistics[$task['id']], 'pureTime')) / 60, 1),
                        'finishStatus' => empty($taskResults[$task['id']]) ? $this->getFinishStatus([]) : $this->getFinishStatus($taskResults[$task['id']]),
                    ];
                    $classroomName = $courseSetName = $courseName = '';
                }
            }
        }

        return ArrayToolkit::group($memberTaskData, 'userId');
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

    private function trans($name)
    {
        $translator = $this->container->get('translator');

        return $translator->trans($name);
    }

    private function getTaskTypes()
    {
        return $this->container->get('course.extension')->getTaskTypes();
    }

    private function getTaskOptional($isOptional)
    {
        $translator = $this->container->get('translator');
        if ($isOptional) {
            return $translator->trans('user.learn.statistics.lesson.optional');
        }

        return $translator->trans('user.learn.statistics.lesson.compulsory');
    }

    private function getFinishStatus($taskResult)
    {
        $translator = $this->container->get('translator');
        if (empty($taskResult['finishedTime'])) {
            return $translator->trans('user.learn.statistics.lesson.un_finish');
        }

        return $translator->trans('user.learn.statistics.lesson.finish');
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

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->getBiz()->service('Task:TaskService');
    }

    /**
     * @return TaskResultService
     */
    protected function getTaskResultService()
    {
        return $this->getBiz()->service('Task:TaskResultService');
    }
}
