<?php


namespace ApiBundle\Api\Resource\MultiClass;


use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Common\CommonException;
use Biz\Course\Service\LessonService;
use Biz\MultiClass\MultiClassException;
use Biz\MultiClass\Service\MultiClassService;
use Biz\Task\Service\TaskService;
use Biz\Task\TaskException;

class MultiClassLesson extends AbstractResource
{
    public function remove(ApiRequest $request, $taskId)
    {
        $task = $this->getTaskService()->getTask($taskId);

        if (!$task) {
            throw TaskException::NOTFOUND_TASK();
        }

        $this->getTaskService()->deleteTask($taskId);

        return ['success' => true];
    }

    public function update(ApiRequest $request, $multiClassId, $lessonId)
    {
        $multiClass = $this->getMultiClassService()->getMultiClass($multiClassId);
        if (!$multiClass) {
            throw MultiClassException::MULTI_CLASS_NOT_EXIST();
        }

        $type = $request->request->get('type', '');
        if (!$type || !in_array($type, ['publish', 'unpublish'])){
            throw CommonException::ERROR_PARAMETER();
        }

        if ('publish' === $type){
            return $this->getCourseLessonService()->publishLesson($multiClass['courseId'], $lessonId);
        }else{
            return $this->getCourseLessonService()->unpublishLesson($multiClass['courseId'], $lessonId);
        }

    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->service('Task:TaskService');
    }

    /**
     * @return MultiClassService
     */
    protected function getMultiClassService()
    {
        return $this->service('MultiClass:MultiClassService');
    }

    /**
     * @return LessonService
     */
    protected function getCourseLessonService()
    {
        return $this->service('Course:LessonService');
    }
}