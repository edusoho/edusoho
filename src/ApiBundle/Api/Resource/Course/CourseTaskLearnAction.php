<?php

namespace ApiBundle\Api\Resource\Course;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Common\CommonException;
use Biz\Course\Service\CourseService;
use Biz\System\Service\SettingService;
use Biz\Task\Service\TaskService;
use Biz\Visualization\Service\DataCollectService;
use Biz\Visualization\Service\LearnControlService;

class CourseTaskLearnAction extends AbstractResource
{
    const ACTION_CHECK = 'check';

    public function get(ApiRequest $request, $courseId, $taskId, $action)
    {
        if (self::ACTION_CHECK !== $action) {
            throw CommonException::ERROR_PARAMETER();
        }
        $user = $this->getCurrentUser();
        $course = $this->getCourseService()->getCourse($courseId);
        $task = $this->getTaskService()->getTask($taskId);
        if (empty($course) || empty($task)) {
            $allowLearn = false;
            $denyReason = 'course_task_not_exist';
        } else {
            list($allowLearn, $denyReason) = $this->getLearnControlService()->checkCreateNewFlow($user['id'], $request->query->get('lastSign', ''));
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
     * @return LearnControlService
     */
    protected function getLearnControlService()
    {
        return $this->service('Visualization:LearnControlService');
    }
}
