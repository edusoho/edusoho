<?php

namespace AppBundle\Extensions\DataTag;

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
            throw new \InvalidArgumentException('参数缺失');
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
        $members = $this->getCourseMemberService()->searchMembers(array('userId' => $userId), array('classroomId' => 'DESC', 'createdTime' => 'DESC'), 0, PHP_INT_MAX);
        if (empty($members)) {
            return $sortedCourses;
        }

        $courseIds = ArrayToolkit::column($members, 'courseId');

        $courses = $this->getCourseService()->findCoursesByIds($courseIds);
        usort($courses, function ($c1, $c2) {
            return $c1['parentId'] < $c2['parentId'];
        });
        $classroomRefs = $this->getClassroomService()->findClassroomsByCoursesIds($courseIds);
        $classroomRefs = ArrayToolkit::index($classroomRefs, 'courseId');

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

                $classroom = $this->getClassroomService()->getClassroom($classroomId);
                if (empty($classrooms['class-'.$classroomId])) {
                    $classroom['tasks'] = array();
                    $classroom['allTaskNum'] = 0;
                    $classroom['learnedTaskNum'] = 0;
                    $classrooms['class-'.$classroomId] = $classroom;
                    $classroomIndex += 1;
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
        $tasks = $this->getTaskService()->findToLearnTasksByCourseIdForMission($courseId);
        if (empty($tasks)) {
            return null;
        }

        $finishTaskCount = $this->getTaskResultService()->countTaskResults(array(
            'userId' => $userId,
            'courseId' => $courseId,
            'status' => 'finish',
        ));

        return array($tasks[0], $finishTaskCount);
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

        return array($this->sortTasks($course, array_slice($tasks, 0, $count)), $finishTaskCount);
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

    protected function getClassroomService()
    {
        return $this->getServiceKernel()->createService('Classroom:ClassroomService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course:CourseService');
    }

    protected function getCourseMemberService()
    {
        return $this->getServiceKernel()->createService('Course:MemberService');
    }

    /**
     * @return TaskResultService
     */
    protected function getTaskResultService()
    {
        return $this->getServiceKernel()->createService('Task:TaskResultService');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->getServiceKernel()->createService('Task:TaskService');
    }
}
