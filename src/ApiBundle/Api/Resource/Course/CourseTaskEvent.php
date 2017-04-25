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
    }

    private function start(ApiRequest $request, $courseId, $taskId, $eventName)
    {
        $this->doing($request, $courseId, $taskId, $eventName);
    }

    private function doing(ApiRequest $request, $courseId, $taskId, $eventName)
    {
        if (!$request->request->get('lastTime')) {
            throw new InvalidArgumentException();
        }

        $this->getCourseService()->tryTakeCourse($courseId);

        // TODO  API无session，无法与Web端业务一致
        $result = $this->getTaskService()->trigger($taskId, $eventName, array(
            'lastTime' => $request->request->get('lastTime')
        ));


        if ($result['status'] == self::EVENT_FINISH) {
            $task = $this->getTaskService()->getTask($taskId);
            list($course, $nextTask, $finishedRate) = $this->getNextTaskAndFinishedRate($task);
        } else {
            $nextTask = null;
            $finishedRate = null;
        }

        return array(
            'result' => $result,
            'event' => $eventName,
            'nextTask' => $nextTask,
            'finishedRate' => $finishedRate
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

        list($course, $nextTask, $finishedRate) = $this->getNextTaskAndFinishedRate($task);

        return array(
            'result' => $result,
            'event' => $eventName,
            'nextTask' => $nextTask,
            'finishedRate' => $finishedRate
        );
    }

    private function getNextTaskAndFinishedRate($task)
    {
        $nextTask = $this->getTaskService()->getNextTask($task['id']);
        $course = $this->getCourseService()->getCourse($task['courseId']);

        $finishedRate = $this->calculateProgress($task['courseId']);

        return array($course, $nextTask, $finishedRate);
    }

    private function calculateProgress($courseId)
    {
        $progress = 0;

        $conditions = array(
            'courseId' => $courseId,
            'status' => 'published',
            'isOptional' => 0,
        );

        $taskCount = $this->getTaskService()->countTasks($conditions);
        if (empty($taskCount)) {
            return $progress;
        }

        $conditions = array(
            'courseId' => $courseId,
            'userId' => $this->getCurrentUser()->getId(),
            'status' => 'finish',
        );
        $finishedCount = $this->getTaskResultService()->countTaskResults($conditions);

        $progress = intval($finishedCount / $taskCount * 100);

        return $progress > 100 ? 100 : $progress;
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