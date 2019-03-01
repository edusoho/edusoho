<?php

namespace AppBundle\Extensions\DataTag;

use Biz\Common\CommonException;
use Biz\Task\Service\TaskResultService;
use Biz\Task\Service\TaskService;
use AppBundle\Common\ArrayToolkit;

/**
 * @deprecated
 * @see StudyCenterMissionsDataTag
 */
class CourseMissionsDataTag extends BaseDataTag implements DataTag
{
    /**
     * 获取我的班级中未学的任务
     * 可传入的参数：
     *   userId         用户
     *   count          课程数量
     *   missionCount   任务数量.
     *
     * @param array $arguments 参数
     *
     * @return array 按课程分组的任务列表
     */
    public function getData(array $arguments)
    {
        if (!ArrayToolkit::requireds($arguments, array('userId', 'count', 'missionCount'))) {
            throw CommonException::ERROR_PARAMETER_MISSING();
        }

        return $this->getCourseStudyMissions($arguments);
    }

    private function getCourseStudyMissions($arguments)
    {
        $userId = $arguments['userId'];

        $sortedCourses = array();

        $courseMemConditions = array(
            'userId' => $userId,
            'locked' => 0,
            'classroomId' => 0,
            'role' => 'student',
        );

        $searchMembers = $this->getCourseMemberService()->searchMembers($courseMemConditions, array('createdTime' => 'DESC'), 0, 5);
        $courseIds = ArrayToolkit::column($searchMembers, 'courseId');

        if (!empty($courseIds)) {
            $courseConditions = array(
                'courseIds' => $courseIds,
                'parentId' => 0,
            );
            $courses = $this->getCourseService()->searchCourses($courseConditions, 'default', 0, $arguments['count']);
            $courses = ArrayToolkit::index($courses, 'id');

            foreach ($searchMembers as $member) {
                if (empty($courses[$member['courseId']])) {
                    continue;
                }

                $course = $courses[$member['courseId']];
                $sortedCourses[] = $course;
            }

            foreach ($sortedCourses as $key => &$course) {
                $conditions = array(
                    'userId' => $userId,
                    'courseId' => $course['id'],
                    'status' => 'finish',
                );

                $finishTaskCount = $this->getTaskResultService()->countTaskResults($conditions);

                $toLearnTasks = $this->getTaskService()->findToLearnTasksByCourseIdForMission($course['id']);

                $course['tasks'] = $this->sortTasks($course, $toLearnTasks);
                $course['finishTaskNum'] = $finishTaskCount;
            }
        }

        return $sortedCourses;
    }

    protected function sortTasks($course, $toLearnTasks)
    {
        if (!$course['isDefault'] || empty($toLearnTasks)) {
            return $toLearnTasks;
        }
        //由于默认教学计划可能会有多个任务聚合在一个任务下，它们共享相同的number，展示时需要动态计算
        $tasks = $this->getTaskService()->findTasksByCourseId($course['id']);
        foreach ($tasks as $index => $task) {
            foreach ($toLearnTasks as &$toLearnTask) {
                if ($toLearnTask['id'] == $task['id']) {
                    $toLearnTask['number'] = $index + 1;
                    break;
                }
            }
        }

        return $toLearnTasks;
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->getBiz()->service('Course:CourseService');
    }

    protected function getCourseMemberService()
    {
        return $this->getServiceKernel()->getBiz()->service('Course:MemberService');
    }

    /**
     * @return TaskResultService
     */
    protected function getTaskResultService()
    {
        return $this->getServiceKernel()->getBiz()->service('Task:TaskResultService');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->getServiceKernel()->getBiz()->service('Task:TaskService');
    }
}
