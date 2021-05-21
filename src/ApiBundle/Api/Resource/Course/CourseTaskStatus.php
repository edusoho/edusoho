<?php


namespace ApiBundle\Api\Resource\Course;


use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Common\CommonException;
use Biz\Course\CourseException;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\LessonService;
use Biz\Task\Service\TaskService;
use Biz\Task\TaskException;

class CourseTaskStatus extends AbstractResource
{
    public function update(ApiRequest $request, $courseId, $taskId)
    {
        $course = $this->getCourseService()->getCourse($courseId);
        if (!$course) {
            throw CourseException::NOTFOUND_COURSE();
        }

        $task = $this->getTaskService()->getTask($taskId);
        if (!$task) {
            throw TaskException::NOTFOUND_TASK();
        }
        $lessonId = $task['categoryId'];

        $type = $request->request->get('type', '');
        if (!$type || !in_array($type, ['publish', 'unpublish'])){
            throw CommonException::ERROR_PARAMETER();
        }

        if ('publish' === $type){
            return $this->getCourseLessonService()->publishLesson($courseId, $lessonId);
        }else{
            return $this->getCourseLessonService()->unpublishLesson($courseId, $lessonId);
        }

    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->service('Course:CourseService');
    }

    /**
     * @return LessonService
     */
    protected function getCourseLessonService()
    {
        return $this->service('Course:LessonService');
    }

    /**
     * @return TaskService
     */
    private function getTaskService()
    {
        return $this->service('Task:TaskService');
    }
}