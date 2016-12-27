<?php


namespace AppBundle\Controller;


use Biz\Activity\Service\ActivityService;
use Biz\Course\Service\CourseService;
use Biz\Note\Service\CourseNoteService;
use Biz\Task\Service\TaskService;
use Symfony\Component\HttpFoundation\Request;

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

        $activity = $this->getActivityService()->getActivity($task['activityId']);
        $tasks    = $this->getTaskService()->findTasksFetchActivityAndResultByCourseId($courseId);
        return $this->render('task/plugin/task-list.html.twig', array(
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

        $note = $this->getNoteService()->getCourseNoteByUserIdAndTaskId($currentUser['id'], $taskId);

        return $this->render('task/plugin/note.html.twig', array(
            'note' => $note,
            'task' => $task
        ));
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->createService('Activity:ActivityService');
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

    /**
     * @return CourseNoteService
     */
    protected function getNoteService()
    {
        return $this->createService('Note:CourseNoteService');
    }

}

