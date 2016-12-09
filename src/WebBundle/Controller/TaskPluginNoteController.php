<?php


namespace WebBundle\Controller;


use Biz\Course\Service\CourseService;
use Biz\Note\Service\CourseNoteService;
use Biz\Task\Service\TaskService;
use Symfony\Component\HttpFoundation\Request;

class TaskPluginNoteController extends BaseController
{
    public function noteAction(Request $request, $courseId, $taskId)
    {
        $currentUser = $this->getUser();

        $this->getCourseService()->tryTakeCourse($courseId);

        $task = $this->getTaskService()->getTask($taskId);

        $note = $this->getNoteService()->getUserTaskNote($currentUser['id'], $taskId);

        return $this->render('WebBundle:TaskPlugin:note.html.twig', array(
            'note' => $note,
            'task' => $task
        ));
    }

    /**
     * @return CourseNoteService
     */
    protected function getNoteService()
    {
        return $this->createService('Note:CourseNoteService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->createService('Task:TaskService');
    }

}

