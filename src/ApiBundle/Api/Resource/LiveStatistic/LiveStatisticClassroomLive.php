<?php

namespace ApiBundle\Api\Resource\LiveStatistic;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use Biz\Activity\Service\ActivityService;
use Biz\Classroom\Service\ClassroomService;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\Task\Service\TaskService;

class LiveStatisticClassroomLive extends AbstractResource
{
    public function search(ApiRequest $request, $classroomId)
    {
        $this->getClassroomService()->tryManageClassroom($classroomId);
        $tasks = $this->buildClassLiveTasks($request, $classroomId);
        list($offset, $limit) = $this->getOffsetAndLimit($request);
        $tasks = array_slice($tasks, $offset, $limit);

        return $this->makePagingObject($this->processTasksData($tasks), count($tasks), $offset, $limit);
    }

    protected function processTasksData($tasks)
    {
        $courseIds = ArrayToolkit::column($tasks, 'courseId');
        $courses = $this->getCourseService()->findCoursesByIds($courseIds);
        $courses = ArrayToolkit::index($courses, 'id');
        $courseSetIds = ArrayToolkit::column($tasks, 'fromCourseSetId');
        $courseSets = $this->getCourseSetService()->findCourseSetsByIds($courseSetIds);
        $courseSets = ArrayToolkit::index($courseSets, 'id');
        foreach ($tasks as &$liveTask) {
            $course = $courses[$liveTask['courseId']];
            $courseSet = $courseSets[$liveTask['fromCourseSetId']];
            $liveTask['courseTitle'] = empty($course['title']) ? $courseSet['title'] : $course['title'];
            $liveTask['maxStudentNum'] = $course['maxStudentNum'];
            $liveTask['status'] = $liveTask['startTime'] > time() ? 'coming' : ($liveTask['endTime'] < time() ? 'finished' : 'playing');
        }

        return $tasks;
    }

    protected function buildClassLiveTasks($request, $classroomId)
    {
        $courses = $this->getClassroomService()->findByClassroomId($classroomId);
        $courseIds = empty($courses) ? [-1] : ArrayToolkit::column($courses, 'courseId');
        $taskConditions = [
            'courseIds' => $courseIds,
            'type' => 'live',
            'titleLike' => $request->query->get('title'),
            'status' => 'published',
        ];

        $liveTasks = $this->getTaskService()->searchTasks(
            $taskConditions,
            ['startTime' => 'DESC'],
            0,
            PHP_INT_MAX,
            ['id', 'startTime', 'endTime', 'length', 'title', 'courseId', 'fromCourseSetId']
        );
        $doingArr = [];
        $endArr = [];
        $noStart = [];
        // 按直播开始时间首先按进行中、已结束、未开始状态排序，再按直播开始时间倒叙排序
        foreach ($liveTasks as $liveTask) {
            if ($liveTask['startTime'] < time() && $liveTask['endTime'] > time()) {
                $doingArr[] = $liveTask;
                continue;
            }

            if ($liveTask['endTime'] < time()) {
                $endArr[] = $liveTask;
                continue;
            }

            $noStart[] = $liveTask;
        }

        return array_merge($doingArr, $noStart, $endArr);
    }

    /**
     * @return ClassroomService
     */
    private function getClassroomService()
    {
        return $this->service('Classroom:ClassroomService');
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->service('Course:CourseSetService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->service('Course:CourseService');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->service('Task:TaskService');
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->service('Activity:ActivityService');
    }
}
