<?php

namespace ApiBundle\Api\Resource\Course;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Course\CourseException;
use Biz\Course\Service\CourseService;
use Biz\Task\Service\TaskResultService;
use Biz\Task\Service\TaskService;
use Biz\Task\TaskResultException;

class CourseTaskResult extends AbstractResource
{
    public function get(ApiRequest $request, $courseId, $taskId)
    {
        $course = $this->getCourseService()->getCourse($courseId);

        if (!$course) {
            throw CourseException::NOTFOUND_COURSE();
        }
        $taskResult = $this->getTaskResultService()->getUserTaskResultByTaskId($taskId);

        if (!empty($taskResult)) {
            return $taskResult;
        }
        $task = $this->getTaskService()->getTask($taskId);
        $user = $this->getCurrentUser();
        $taskResult = array(
            'activityId' => $task['activityId'],
            'courseId' => $task['courseId'],
            'courseTaskId' => $task['id'],
            'userId' => $user['id'],
        );

        return $this->getTaskResultService()->createTaskResult($taskResult);
    }

    public function update(ApiRequest $request, $courseId, $taskId)
    {
        $course = $this->getCourseService()->getCourse($courseId);

        if (!$course) {
            throw CourseException::NOTFOUND_COURSE();
        }
        $taskResult = $this->getTaskResultService()->getUserTaskResultByTaskId($taskId);
        if (!$taskResult) {
            throw TaskResultException::NOTFOUND_TASK_RESULT();
        }

        $fields = $request->request->all();

        return $this->getTaskResultService()->updateTaskResult($taskResult['id'], $fields);
    }

    public function search(ApiRequest $request, $courseId)
    {
        $course = $this->getCourseService()->getCourse($courseId);

        if (!$course) {
            throw CourseException::NOTFOUND_COURSE();
        }

        return $this->getTaskResultService()->findUserTaskResultsByCourseId($courseId);
    }

    /**
     * @return TaskResultService
     */
    protected function getTaskResultService()
    {
        return $this->service('Task:TaskResultService');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->service('Task:TaskService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->service('Course:CourseService');
    }
}
