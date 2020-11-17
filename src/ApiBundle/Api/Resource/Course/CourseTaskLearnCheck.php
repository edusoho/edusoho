<?php

namespace ApiBundle\Api\Resource\Course;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Course\Service\CourseService;
use Biz\System\Service\SettingService;
use Biz\Task\Service\TaskService;
use Biz\Visualization\Service\DataCollectService;

class CourseTaskLearnCheck extends AbstractResource
{
    public function get(ApiRequest $request, $courseId, $taskId)
    {
        $course = $this->getCourseService()->getCourse($courseId);
        $task = $this->getTaskService()->getTask($taskId);
        if (empty($course) || empty($task)) {
            $allowLearn = false;
            $denyReason = 'course_task_item';
        }

        return [
            'allowLearn' => $allowLearn,
            'denyReason' => $denyReason,
        ];
    }

    /**
     * @return DataCollectService
     */
    private function getDataCollectService()
    {
        return $this->service('Visualization:DataCollectService');
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
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->service('Setting:SettingService');
    }
}
