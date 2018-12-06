<?php

namespace ApiBundle\Api\Resource\Course;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Common\CommonException;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\LearningDataAnalysisService;
use Biz\Task\Service\TaskResultService;
use Biz\Task\Service\TaskService;
use Biz\Task\TaskException;

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
            throw CommonException::ERROR_PARAMETER();
        }

        $taskResult = $this->getTaskResultService()->getUserTaskResultByTaskId($taskId);

        if (!$taskResult) {
            $this->start($request, $courseId, $taskId, self::EVENT_START);
        }

        if (self::EVENT_DOING == $eventName) {
            return $this->doing($request, $courseId, $taskId, $eventName);
        }

        if (self::EVENT_FINISH == $eventName) {
            return $this->finish($request, $courseId, $taskId, $eventName);
        }

        throw CommonException::ERROR_PARAMETER();
    }

    private function start(ApiRequest $request, $courseId, $taskId, $eventName)
    {
        $this->doing($request, $courseId, $taskId, $eventName);
    }

    private function doing(ApiRequest $request, $courseId, $taskId, $eventName)
    {
        $this->getCourseService()->tryTakeCourse($courseId);

        $lastTime = $request->request->get('lastTime', time());
        $watchTime = $request->request->get('watchTime', 0);

        $data = array('lastTime' => $lastTime);
        if (!empty($watchTime)) {
            $data['events']['watching']['watchTime'] = $watchTime;
        }

        $result = $this->getTaskService()->trigger($taskId, $eventName, $data);

        if (self::EVENT_FINISH == $result['status']) {
            $nextTask = $this->getTaskService()->getNextTask($taskId);
            $progress = $this->getLearningDataAnalysisService()->getUserLearningProgress($courseId, $result['userId']);
            $completionRate = $progress['percent'];
        } else {
            $nextTask = null;
            $completionRate = null;
        }

        return array(
            'result' => $result,
            'event' => $eventName,
            'nextTask' => $nextTask,
            'lastTime' => time(),
            'completionRate' => $completionRate,
        );
    }

    private function finish(ApiRequest $request, $courseId, $taskId, $eventName)
    {
        $this->getCourseService()->tryTakeCourse($courseId);

        $task = $this->getTaskService()->getTask($taskId);

        if ('published' != $task['status']) {
            throw TaskException::UNPUBLISHED_TASK();
        }

        $result = $this->getTaskService()->finishTaskResult($taskId);

        $nextTask = $this->getTaskService()->getNextTask($taskId);
        $learningProgress = $this->getLearningDataAnalysisService()->getUserLearningProgress($courseId, $result['userId']);

        return array(
            'result' => $result,
            'event' => $eventName,
            'nextTask' => $nextTask ?: null,
            'completionRate' => $learningProgress['percent'],
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

    /**
     * @return LearningDataAnalysisService
     */
    private function getLearningDataAnalysisService()
    {
        return $this->service('Course:LearningDataAnalysisService');
    }
}
