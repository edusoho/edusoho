<?php

namespace ApiBundle\Api\Resource\Course;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Activity\Service\ActivityService;
use Biz\Course\CourseException;
use Biz\Task\Service\TaskResultService;
use Biz\Task\Service\TaskService;
use Biz\Task\TaskException;

class CourseTask extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function search(ApiRequest $request, $courseId)
    {
        $course = $this->service('Course:CourseService')->getCourse($courseId);

        if (!$course) {
            throw CourseException::NOTFOUND_COURSE();
        }

        return $this->service('Task:TaskService')->findTasksByCourseId($courseId);
    }

    public function get(ApiRequest $request, $courseId, $taskId)
    {
        $task = $this->getTaskService()->getTask($taskId);

        if (!$task) {
            throw TaskException::NOTFOUND_TASK();
        }

        $task['activity'] = $this->getActivityService()->getActivity($task['activityId'], true);
        $task['activity']['finishCondition'] = $this->getActivityService()->getActivityFinishCondition($task['activity']);
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
