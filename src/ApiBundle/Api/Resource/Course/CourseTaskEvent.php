<?php

namespace ApiBundle\Api\Resource\Course;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Exception\InvalidArgumentException;
use ApiBundle\Api\Exception\ResourceNotFoundException;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Course\Service\CourseService;
use Biz\Task\Service\TaskResultService;
use Biz\Task\Service\TaskService;

class CourseTaskEvent extends AbstractResource
{
    const EVENT_START = 'start';
    const EVENT_DOING = 'doing';
    const EVENT_FINISH = 'finish';

    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function update(ApiRequest $request, $courseId, $taskId, $eventName)
    {
        if (!in_array($eventName, array(self::EVENT_DOING, self::EVENT_FINISH))) {
            throw new InvalidArgumentException();
        }

        $taskResult = $this->getTaskResultService()->getUserTaskResultByTaskId($taskId);

        if (!$taskResult) {
            $this->start($request, $courseId, $taskId, self::EVENT_START);
        }

        if ($eventName == self::EVENT_DOING) {
            return $this->doing($request, $courseId, $taskId, $eventName);
        }

        if ($eventName == self::EVENT_FINISH) {
            return $this->finish($request, $courseId, $taskId, $eventName);
        }

        throw new InvalidArgumentException();
    }

    private function start(ApiRequest $request, $courseId, $taskId, $eventName)
    {
        $this->doing($request, $courseId, $taskId, $eventName);
    }

    private function doing(ApiRequest $request, $courseId, $taskId, $eventName)
    {
        $this->getCourseService()->tryTakeCourse($courseId);

        // TODO  API无session，无法与Web端业务一致
        $result = $this->getTaskService()->trigger($taskId, $eventName, array(
            'lastTime' => time()
        ));

        if ($result['status'] == self::EVENT_FINISH) {
            $nextTask = $this->getTaskService()->getNextTask($taskId);
            $completionRate = $this->getTaskService()->getUserTaskCompletionRate($taskId);
        } else {
            $nextTask = null;
            $completionRate = null;
        }

        return array(
            'result' => $result,
            'event' => $eventName,
            'nextTask' => $nextTask,
            'completionRate' => $completionRate
        );
    }

    private function finish(ApiRequest $request, $courseId, $taskId, $eventName)
    {
        $this->getCourseService()->tryTakeCourse($courseId);

        $task = $this->getTaskService()->getTask($taskId);

        if ($task['status'] != 'published') {
            throw new ResourceNotFoundException('Task not publish');
        }

        $result = $this->getTaskService()->finishTaskResult($taskId);

        return array(
            'result' => $result,
            'event' => $eventName,
            'nextTask' => $this->getTaskService()->getNextTask($taskId),
            'completionRate' => $this->getTaskService()->getUserTaskCompletionRate($taskId)
        );
    }

    /**
     * @return TaskResultService
     */
    private function getTaskResultService()
    {
        return $this->service('Task:TaskResultService');
    }

    /**
     * @return CourseService
     */
    private function getCourseService()
    {
        return $this->service('Course:CourseService');
    }

    /**
     * @return TaskService
     */
    private function getTaskService()
    {
        return $this->service('Task:TaskService');
    }
}