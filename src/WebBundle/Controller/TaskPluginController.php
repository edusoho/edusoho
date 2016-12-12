<?php


namespace WebBundle\Controller;


use Biz\Activity\Service\ActivityService;
use Biz\Course\Service\CourseService;
use Biz\Note\Service\CourseNoteService;
use Biz\Task\Service\TaskService;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Controller\BaseController;

class TaskPluginController extends BaseController
{
    public function taskListAction(Request $request, $courseId, $taskId)
    {
        list($course) = $this->getCourseService()->tryTakeCourse($courseId);

        $task = $this->getTaskService()->getTask($taskId);

        if (empty($task)) {
            throw $this->createNotFoundException();
        }

        $preview = $request->query->get('preview', false);

        $activity = $this->getActivityService()->getActivity($task['id']);
        $tasks    = $this->getTaskService()->findUserTasksFetchActivityAndResultByCourseId($courseId);
        return $this->render('@Web/TaskPlugin/task-list.html.twig', array(
            'tasks'    => $tasks,
            'course'   => $course,
            'activity' => $activity,
            'preview'  => $preview
        ));
    }

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
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->getBiz()->service('Activity:ActivityService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->getBiz()->service('Course:CourseService');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->getBiz()->service('Task:TaskService');
    }

    /**
     * @return CourseNoteService
     */
    protected function getNoteService()
    {
        return $this->createService('Note:CourseNoteService');
    }

}

