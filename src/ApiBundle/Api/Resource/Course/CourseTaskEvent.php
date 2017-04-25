<?php

namespace ApiBundle\Api\Resource\Course;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Exception\InvalidArgumentException;
use ApiBundle\Api\Exception\ResourceNotFoundException;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Course\Service\CourseService;
use Biz\Task\Service\TaskService;

class CourseTaskEvent extends AbstractResource
{
    const EVENT_DOING = 'doing';
    const EVENT_FINISH = 'finish';

    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function update(ApiRequest $request, $courseId, $taskId, $eventName)
    {
        if ($eventName != self::EVENT_DOING) {
            throw new InvalidArgumentException();
        }

        if (!$request->request->get('lastTime')) {
            throw new InvalidArgumentException();
        }

        $this->getCourseService()->tryTakeCourse($courseId);

        // TODO  API无session，无法与Web端业务一致
        $result = $this->getTaskService()->trigger($taskId, $eventName, array(
            'lastTime' => $request->request->get('lastTime')
        ));

        return array(
            'result' => $result,
            'event' => $eventName
        );
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