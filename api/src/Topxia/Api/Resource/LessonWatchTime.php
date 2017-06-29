<?php
namespace Topxia\Api\Resource;

use Biz\Course\Service\CourseService;
use Biz\Task\Service\TaskResultService;
use Biz\Task\Service\TaskService;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class LessonWatchTime extends BaseResource
{
    public function post(Application $app, Request $request)
    {
        $lessonId = $request->request->get('lessonId');
        $watchTime = $request->request->get('watchTime', 120);

        $task = $this->getTaskService()->getTask($lessonId);

        if ($task) {
            $this->getCourseService()->tryTakeCourse($task['courseId']);
            $taskResult = $this->getTaskResultService()->getUserTaskResultByTaskId($task['id']);
            if (!$taskResult) {
                $this->getTaskService()->startTask($task['id']);
            }
            $this->getTaskService()->watchTask($task['id'], $watchTime);
        }

        return array(
            'id' => $lessonId,
            'userId' => $this->getCurrentUser()->getId(),
            'courseId' => $request->request->get('courseId', 1),
            'lessonId' => $lessonId,
            'status' => 'learning',
            'startTime' => 0,
            'finishedTime' => 0,
            'learnTime' => 0,
            'watchTime' => 0,
            'watchNum' => 0,
            'videoStatus' => 'paused',
            'updateTime' => '1497844699'
        );
    }

    public function filter($res)
    {
    }

    /**
     * @return CourseService
     */
    private function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course:CourseService');
    }

    /**
     * @return TaskService
     */
    private function getTaskService()
    {
        return $this->getServiceKernel()->createService('Task:TaskService');
    }

    /**
     * @return TaskResultService
     */
    private function getTaskResultService()
    {
        return $this->getServiceKernel()->createService('Task:TaskResultService');
    }
}
