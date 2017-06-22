<?php
namespace Topxia\Api\Resource;

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
            $this->getTaskService()->trigger('doing', $task['id'], array('lastTime' => time() - $watchTime));
        }

        return array(
            'id' => $lessonId,
            'userId' => $this->getCurrentUser()->getId(),
            'courseId' => $request->request->get('courseId'),
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
     * @return TaskService
     */
    private function getTaskService()
    {
        return $this->getServiceKernel()->createService('Task:TaskService');
    }
}
