<?php

namespace ApiBundle\Api\Resource\Course;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\Annotation\ResponseFilter;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Exception\ResourceNotFoundException;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Activity\Service\ActivityService;
use Biz\Task\Service\TaskService;

class CourseTask extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function search(ApiRequest $request, $courseId)
    {
        $course = $this->service('Course:CourseService')->getCourse($courseId);

        if (!$course) {
            throw new ResourceNotFoundException('教学计划不存在');
        }

        return $this->service('Task:TaskService')->findTasksByCourseId($courseId);
    }

    public function get(ApiRequest $request, $courseId, $taskId)
    {
        $task = $this->getTaskService()->getTask($taskId);

        if (!$task) {
            throw new ResourceNotFoundException('任务不存在');
        }

        $task['activity'] = $this->getActivityService()->getActivity($task['activityId'], true);

        return $task;
    }

    /**
     * @return TaskService
     */
    private function getTaskService()
    {
        return $this->service('Task:TaskService');
    }

    /**
     * @return ActivityService
     */
    private function getActivityService()
    {
        return $this->service('Activity:ActivityService');
    }

}