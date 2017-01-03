<?php


namespace AppBundle\Controller;


use Biz\Activity\Service\ActivityService;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\ThreadService;
use Biz\Note\Service\CourseNoteService;
use Biz\Task\Service\TaskService;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;

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

    public function threadsAction(Request $request, $courseId, $taskId)
    {
        list($course, $member) = $this->getCourseService()->tryTakeCourse($courseId);

        $task = $this->getTaskService()->getTask($taskId);

        if (empty($task)) {
            throw $this->createNotFoundException('task not found');
        }

        $threads = $this->getThreadService()->searchThreads(
            array(
                'taskId' => $task['id'],
                'type'   => 'question'
            ),
            array(
                'createdTime' => 'DESC'
            ),
            0, 20
        );

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($threads, 'userId'));

        $form = $this->createQuestionForm(array(
            'courseId' => $course['id'],
            'taskId'   => $taskId
        ));

        return $this->render('task/plugin/questions.html.twig', array(
            'threads' => $threads,
            'task'    => $task,
            'form'    => $form->createView(),
            'users'   => $users
        ));
    }

    private function createQuestionForm(array $data)
    {
        $form = $this->get('form.factory')->createNamedBuilder('question', 'form', $data, array());
        return $form
            ->add('title', 'Symfony\Component\Form\Extension\Core\Type\TextType')
            ->add('content', 'Symfony\Component\Form\Extension\Core\Type\TextareaType')
            ->add('courseId', 'Symfony\Component\Form\Extension\Core\Type\HiddenType')
            ->add('taskId', 'Symfony\Component\Form\Extension\Core\Type\HiddenType')
            ->getForm();
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

    /**
     * @return ThreadService
     */
    protected function getThreadService()
    {
        return $this->createService('Course:ThreadService');
    }
}

