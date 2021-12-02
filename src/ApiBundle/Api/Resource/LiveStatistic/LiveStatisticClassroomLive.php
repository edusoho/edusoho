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
        $activityIds = ArrayToolkit::column($tasks, 'activityId');
        $activities = $this->getActivityService()->findActivities($activityIds, true);
        $activities = ArrayToolkit::index($activities, 'id');
        foreach ($tasks as &$liveTask) {
            $course = $courses[$liveTask['courseId']];
            $liveTask['courseTitle'] = empty(trim($course['title'])) ? $course['courseSetTitle'] : $course['title'];
            $liveTask['maxStudentNum'] = empty($course['maxStudentNum']) ? '无限制' : $course['maxStudentNum'];
            $liveTask['status'] = empty($activities[$liveTask['activityId']]) ? 'finished' : ('closed' == $activities[$liveTask['activityId']]['ext']['progressStatus'] ? 'finished' : ($liveTask['startTime'] > time() ? 'coming' : 'playing'));
            $liveTask['length'] = round(($liveTask['endTime'] - $liveTask['startTime']) / 60, 1);
        }

        return $tasks;
    }

    protected function buildClassLiveTasks(ApiRequest $request, $classroomId)
    {
        $courses = $this->getClassroomService()->findByClassroomId($classroomId);
        $courseIds = empty($courses) ? [-1] : ArrayToolkit::column($courses, 'courseId');
        $courseId = $request->query->get('courseId', 0);
        $taskConditions = [
            'courseIds' => empty($courseId) ? $courseIds : array_intersect($courseIds, [$courseId]),
            'type' => 'live',
            'titleLike' => $request->query->get('title', ''),
            'status' => 'published',
        ];

        $liveTasks = $this->getTaskService()->searchTasks(
            $taskConditions,
            ['startTime' => 'DESC'],
            0,
            PHP_INT_MAX,
            ['id', 'startTime', 'endTime', 'length', 'title', 'courseId', 'fromCourseSetId', 'activityId']
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
