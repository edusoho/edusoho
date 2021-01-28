<?php

namespace AppBundle\Extensions\DataTag;

use Biz\Common\CommonException;
use Biz\Task\Service\TaskService;
use AppBundle\Common\ArrayToolkit;
use Biz\Task\Service\TaskResultService;

class StudyCenterMissionsDataTag extends BaseDataTag implements DataTag
{
    /**
     * 获取我的所有课程中未学的任务
     * 可传入的参数：
     *   userId         用户
     *   count          班级课程和普通课程各自的数量
     *   missionCount   每个课程的任务数量.
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

        return $this->getStudyMissions($arguments);
    }

    private function getStudyMissions($arguments)
    {
        $userId = $arguments['userId'];
        $count = $arguments['count'];
        $missionCount = $arguments['missionCount'];

        $sortedCourses = array();
        //1. 先获取userId的所有course_member数据，获取课程的所属班级
        //2. 对courses按照classroom排序，分离出classroom的courses
        //3. 遍历courses，判断courses是否已学完，如果未学完，则查询出要学的missionCount个任务
        //4. 交给前端展示
        $members = $this->getCourseMemberService()->searchMembers(array('userId' => $userId, 'role' => 'student'), array('classroomId' => 'DESC', 'createdTime' => 'DESC'), 0, PHP_INT_MAX);
        if (empty($members)) {
            return $sortedCourses;
        }

        $courseIds = ArrayToolkit::column($members, 'courseId');

        $courses = $this->getCourseService()->findCoursesByIds($courseIds);
        usort($courses, function ($c1, $c2) use ($courseIds) {
            if (($c1['parentId'] > 0 && $c2['parentId'] > 0) || (0 == $c1['parentId'] && 0 == $c2['parentId'])) {
                return array_search($c1['id'], $courseIds) > array_search($c2['id'], $courseIds);
            }

            return $c1['parentId'] < $c2['parentId'];
        });
        $classroomRefs = $this->getClassroomService()->findClassroomsByCoursesIds($courseIds);
        $classroomRefs = ArrayToolkit::index($classroomRefs, 'courseId');
        $classroomIds = ArrayToolkit::column($classroomRefs, 'classroomId');
        $courseClassrooms = $this->getClassroomService()->findClassroomsByIds($classroomIds);

        $classrooms = array();
        $classroomIndex = 0;
        $courseIndex = 0;
        foreach ($courses as $course) {
            if (!empty($classroomRefs[$course['id']])) {
                //班级课程
                if ($classroomIndex >= $count) {
                    continue;
                }

                $classroomRef = $classroomRefs[$course['id']];
                $classroomId = $classroomRef['classroomId'];
                $classroom = empty($courseClassrooms[$classroomId]) ? array() : $courseClassrooms[$classroomId];
                if (empty($classrooms['class-'.$classroomId])) {
                    $classroom['tasks'] = array();
                    $classroom['allTaskNum'] = 0;
                    $classroom['learnedTaskNum'] = 0;
                    $classrooms['class-'.$classroomId] = $classroom;
                    ++$classroomIndex;
                }
                $classrooms['class-'.$classroomId]['allTaskNum'] += $course['taskNum'];
                $taskData = $this->getTaskDataInClassroomCourse($course['id'], $userId);
                if (!empty($taskData)) {//班级课程下存在未学的任务
                    $classrooms['class-'.$classroomId]['tasks'][] = $taskData[0];
                    $classrooms['class-'.$classroomId]['learnedTaskNum'] += $taskData[1];
                }
            } else {
                if ($courseIndex >= $count) {
                    break;
                }
                //普通课程，包括已经从班级移除的课程
                $tasksData = $this->getTasksDataInCourse($course, $userId, $missionCount);
                $course['finishTaskNum'] = 0;
                if (!empty($tasksData)) {
                    $course['tasks'] = $tasksData[0];
                    $course['finishTaskNum'] += $tasksData[1];
                    $sortedCourses[] = $course;
                    ++$courseIndex;
                }
            }
        }

        return array('courses' => $sortedCourses, 'classrooms' => $classrooms);
    }

    public function getTaskDataInClassroomCourse($courseId, $userId)
    {
        $finishedTasks = $this->getTaskResultService()->findUserFinishedTaskResultsByCourseId($courseId);

        if (!empty($finishedTasks)) {
            $taskIds = ArrayToolkit::column($finishedTasks, 'courseTaskId');
            $electiveTaskIds = $this->getStartElectiveTaskIds($courseId);
            $taskIds = array_merge($taskIds, $electiveTaskIds);

            $conditions = array(
                'courseId' => $courseId,
                'status' => 'published',
                'excludeIds' => $taskIds,
            );

            $tasks = $this->getTaskService()->searchTasks($conditions, array('seq' => 'ASC'), 0, 1);
        } else {
            $tasks = $this->getTaskService()->findTasksByCourseId($courseId);
        }

        $task = empty($tasks) ? array() : array_shift($tasks);
        if ($task) {
            $taskResult = $this->getTaskResultService()->getUserTaskResultByTaskId($task['id']);
            $task['result'] = empty($taskResult) ? array() : $taskResult;
        }

        return array($task, count($finishedTasks));
    }

    public function getTasksDataInCourse($course, $userId, $count)
    {
        $tasks = $this->getTaskService()->findToLearnTasksByCourseIdForMission($course['id']);
        if (empty($tasks)) {
            return null;
        }

        $finishTaskCount = $this->getTaskResultService()->countTaskResults(array(
            'userId' => $userId,
            'courseId' => $course['id'],
            'status' => 'finish',
        ));
        if ($finishTaskCount == $course['taskNum']) {
            return null;
        }

        return array(array_slice($tasks, 0, $count), $finishTaskCount);
    }

    protected function getStartElectiveTaskIds($courseId)
    {
        $userTaskResults = $this->getTaskResultService()->findUserProgressingTaskResultByCourseId($courseId);
        $userTaskIds = ArrayToolkit::column($userTaskResults, 'courseTaskId');

        $conditions = array(
            'courseId' => $courseId,
            'status' => 'published',
            'isOptional' => 1,
        );

        $electiveTasks = $this->getTaskService()->searchTasks($conditions, null, 0, PHP_INT_MAX);
        $electiveTaskIds = ArrayToolkit::column($electiveTasks, 'id');

        $electiveIds = array_intersect($userTaskIds, $electiveTaskIds);

        return empty($electiveIds) ? array() : $electiveIds;
    }

    protected function getClassroomService()
    {
        return $this->getServiceKernel()->getBiz()->service('Classroom:ClassroomService');
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
