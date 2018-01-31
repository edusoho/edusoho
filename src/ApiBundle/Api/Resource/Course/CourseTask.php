<?php

namespace ApiBundle\Api\Resource\Course;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Exception\ErrorCode;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Activity\Service\ActivityService;
use Biz\Task\Service\TaskResultService;
use Biz\Task\Service\TaskService;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CourseTask extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function search(ApiRequest $request, $courseId)
    {
        $course = $this->service('Course:CourseService')->getCourse($courseId);

        if (!$course) {
            throw new NotFoundHttpException('教学计划不存在', null, ErrorCode::RESOURCE_NOT_FOUND);
        }

        return $this->service('Task:TaskService')->findTasksByCourseId($courseId);
    }

    public function get(ApiRequest $request, $courseId, $taskId)
    {
        $task = $this->getTaskService()->getTask($taskId);

        if (!$task) {
            throw new NotFoundHttpException('任务不存在', null, ErrorCode::RESOURCE_NOT_FOUND);
        }

        $task['activity'] = $this->getActivityService()->getActivity($task['activityId'], true);
        $task['result'] = $this->getTaskResultService()->getUserTaskResultByTaskId($taskId);

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
     * @return TaskResultService
     */
    private function getTaskResultService()
    {
        return $this->service('Task:TaskResultService');
    }

    /**
     * @return ActivityService
     */
    private function getActivityService()
    {
        return $this->service('Activity:ActivityService');
    }
}
