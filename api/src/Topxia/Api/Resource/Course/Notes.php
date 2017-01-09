<?php

namespace Topxia\Api\Resource\Course;

use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseNoteService;
use Biz\Task\Service\TaskService;
use Codeages\Biz\Framework\Service\Exception\NotFoundException;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Api\Resource\BaseResource;
use Topxia\Common\ArrayToolkit;

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
            isset($fields['start']) ? (int)$fields['start'] : 0,
            isset($fields['limit']) ? (int)$fields['limit'] : 20
        );

        return $this->wrap($this->filter($notes), $total);
    }

    public function post(Application $app, Request $request, $courseId)
    {
        $requiredFields = array('taskId', 'content');
        $fields         = $this->checkRequiredFields($requiredFields, $request->request->all());

        $task = $this->getTaskService()->getTask($fields['taskId']);

        if (empty($task)) {
            throw new \Exception("ID为{$fields['taskId']}的任务不存在");
        }

        $course = $this->getCourseService()->getCourse($task['courseId']);

        if (empty($course)) {
            throw new NotFoundException(sprintf('course #%s not found', $task['courseId']));
        }

        $note = array(
            'courseId' => $task['courseId'],
            'taskId'   => $task['id'],
            'status'   => !empty($fields['status']) ? 1 : 0,
            'content'  => $fields['content']
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
