<?php

namespace Topxia\Api\Resource\Course;

use Silex\Application;
use Biz\Task\Service\TaskService;
use AppBundle\Common\ArrayToolkit;
use Biz\Course\Service\CourseService;
use Topxia\Api\Resource\BaseResource;
use Biz\Course\Service\CourseNoteService;
use Symfony\Component\HttpFoundation\Request;

class Notes extends BaseResource
{
    public function get(Application $app, Request $request, $courseId)
    {
        $fields = $request->query->all();

        $conditions = ArrayToolkit::parts($fields, array('userId', 'lessonId'));
        if (empty($conditions)) {
            throw new \Exception("userId/lessonId必须指定其中之一");
        }

        $conditions['courseId'] = $courseId;

        $total = $this->getCourseNoteService()->countCourseNotes($conditions);
        $notes = $this->getCourseNoteService()->searchNotes(
            $conditions,
            array('createdTime' => 'DESC'),
            isset($fields['start']) ? (int) $fields['start'] : 0,
            isset($fields['limit']) ? (int) $fields['limit'] : 20
        );

        return $this->wrap($this->filter($notes), $total);
    }

    public function post(Application $app, Request $request, $courseId)
    {
        $requiredFields = array('lessonId', 'content');
        $fields = $this->checkRequiredFields($requiredFields, $request->request->all());

        $task = $this->getTaskService()->getTask($fields['lessonId']);

        if (empty($task)) {
            return $this->error('600001', '课时不存在');
        }

        $course = $this->getCourseService()->getCourse($task['courseId']);

        if (empty($course)) {
            return $this->error('600002', '课程不存在');
        }

        $note = array(
            'courseId' => $task['courseId'],
            'taskId' => $task['id'],
            'status' => !empty($fields['status']) ? 1 : 0,
            'content' => $fields['content'],
        );

        $note = $this->getCourseNoteService()->saveNote($note);
        return $this->callFilter('Course/Note', $note);
    }

    public function filter($res)
    {
        return $this->multicallFilter('Course/Note', $res);
    }

    /**
     * @return CourseNoteService
     */
    protected function getCourseNoteService()
    {
        return $this->getServiceKernel()->createService('Course:CourseNoteService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course:CourseService');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->getServiceKernel()->createService('Task:TaskService');
    }
}
