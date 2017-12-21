<?php

namespace AppBundle\Extensions\DataTag;

use Biz\Course\Service\CourseService;
use Biz\Task\Service\TaskResultService;
use Biz\Task\Service\TaskService;
use AppBundle\Common\ArrayToolkit;

/**
 * @deprecated
 * @see StudyCenterMissionsDataTag
 */
class ClassroomMissionsDataTag extends BaseDataTag implements DataTag
{
    /**
     * 获取我的班级中未学的任务
     * 可传入的参数：
     *   userId         用户
     *   count          班级数量
     *   missionCount   任务数量.
     *
     * @param array $arguments 参数
     *
     * @return array 按班级分组的任务列表
     */
    public function getData(array $arguments)
    {
        if (!ArrayToolkit::requireds($arguments, array('userId', 'count', 'missionCount'))) {
            throw new \InvalidArgumentException('参数缺失');
        }

        return $this->getClassroomStudyMissions($arguments);
    }

    private function getClassroomStudyMissions($arguments)
    {
        $userId = $arguments['userId'];

        $members = $this->getClassroomService()->searchMembers(
            array(
                'userId' => $userId,
                'locked' => 0,
                'role' => 'student',
            ),
            array('createdTime' => 'DESC'),
            0,
            $arguments['count']
        );

        $classroomIds = ArrayToolkit::column($members, 'classroomId');

        if (empty($classroomIds)) {
            return array();
        }

        $sortedClassrooms = array();

        $sortedClassrooms = $this->getSortedClassrooms($classroomIds, $members, $sortedClassrooms);

        foreach ($sortedClassrooms as $key => &$classroom) {
            $courses = $this->getClassroomService()->findActiveCoursesByClassroomId($classroom['id']);

            if (empty($courses)) {
                unset($sortedClassrooms[$key]);
                continue;
            }

            $courseIds = ArrayToolkit::column($courses, 'id');

            /**
             * 找出学过的任务
             */
            $learnedConditions = array(
                'userId' => $userId,
                'courseIds' => $courseIds,
            );
            
            $taskCount = $this->getTaskResultService()->countTaskResults($learnedConditions);
           
            $tasks = $this->getTaskResultService()->searchTaskResults(
                $learnedConditions,
                array('finishedTime' => 'ASC'),
                0,
                $taskCount
            );

            $taskGroupStatus = ArrayToolkit::group($tasks, 'status');
            $learningTaskIds = $this->getLearningTaskIds($taskGroupStatus);
            $finishTaskIds = $this->getFinishTaskIds($taskGroupStatus);

            $notLearnedConditions = array(
                'status' => 'published',
                'courseIds' => $courseIds,
                'excludeIds' => $finishTaskIds,
            );

            $notLearnedTasks = $this->getTaskService()->searchTasks(
                $notLearnedConditions,
                array('seq' => 'ASC'),
                0,
                $arguments['missionCount']
            );

            if (empty($notLearnedTasks)) {
                unset($sortedClassrooms[$key]);
                continue;
            }
            foreach ($notLearnedTasks as &$task) {
                if (in_array($task['id'], $learningTaskIds)) {
                    $task['isLearned'] = 'learning';
                } else {
                    $task['isLearned'] = '';
                }
                $canLearn = $this->getTaskService()->canLearnTask($task['id']);
                $task['lock'] = empty($canLearn);
            }

            $classroom['tasks'] = $notLearnedTasks;
            $classroom['learnedTaskNum'] = count($finishTaskIds);
            $classroom['allTaskNum'] = $this->getTotalTaskCount($courses);
        }

        return $sortedClassrooms;
    }

    protected function getClassroomService()
    {
        return $this->getServiceKernel()->getBiz()->service('Classroom:ClassroomService');
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

    /**
     * @param $classroomIds
     * @param $members
     * @param $sortedClassrooms
     *
     * @return array
     */
    private function getSortedClassrooms($classroomIds, $members, $sortedClassrooms)
    {
        $classrooms = $this->getClassroomService()->findClassroomsByIds($classroomIds);
        $classrooms = ArrayToolkit::index($classrooms, 'id');
        foreach ($members as $member) {
            if (empty($classrooms[$member['classroomId']])) {
                continue;
            }

            $sortedClassrooms[] = $classrooms[$member['classroomId']];
        }

        return $sortedClassrooms;
    }

    /**
     * @param $taskGroupStatus
     *
     * @return array
     */
    private function getFinishTaskIds($taskGroupStatus)
    {
        $finishTasks = isset($taskGroupStatus['finish']) ? $taskGroupStatus['finish'] : array();
        $finishTaskIds = ArrayToolkit::column($finishTasks, 'courseTaskId');

        return $finishTaskIds;
    }

    /**
     * @param $taskGroupStatus
     *
     * @return array
     */
    private function getLearningTaskIds($taskGroupStatus)
    {
        $learningTasks = isset($taskGroupStatus['start']) ? $taskGroupStatus['start'] : array();
        $learningTaskIds = ArrayToolkit::column($learningTasks, 'courseTaskId');

        return array($taskGroupStatus, $learningTaskIds);
    }

    /**
     * @param $courses
     *
     * @return int
     */
    private function getTotalTaskCount($courses)
    {
        $classroomTaskNum = 0;

        foreach ($courses as $course) {
            $classroomTaskNum += $course['taskNum'];
        }

        return $classroomTaskNum;
    }
}
