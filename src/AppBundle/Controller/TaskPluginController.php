<?php

namespace AppBundle\Controller;

use Biz\Activity\Service\ActivityService;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\ThreadService;
use Biz\Course\Service\CourseNoteService;
use Biz\Task\Service\TaskService;
use Biz\Task\TaskException;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;

class TaskPluginController extends BaseController
{
    public function taskListAction(Request $request, $courseId, $taskId)
    {
        list($course, $member) = $this->getCourseService()->tryTakeCourse($courseId);

        $task = $this->getTaskService()->getTask($taskId);

        if (empty($task)) {
            $this->createNewException(TaskException::NOTFOUND_TASK());
        }

        $preview = $request->query->get('preview', false);

        $activity = $this->getActivityService()->getActivity($task['activityId']);
        list($courseItems, $nextOffsetSeq) = $this->getCourseService()->findCourseItemsByPaging($courseId, array('limit' => 10000));

        return $this->render('task/plugin/task-list.html.twig', array(
            'courseItems' => $courseItems,
            'nextOffsetSeq' => $nextOffsetSeq,
            'course' => $course,
            'member' => $member,
            'currentTaskId' => $taskId,
            'preview' => $preview,
        ));
    }

    public function taskListByPagingAction(Request $request, $courseId)
    {
        list($course) = $this->getCourseService()->tryTakeCourse($courseId);

        $offsetSeq = $request->query->get('offsetSeq');
        $direction = $request->query->get('direction', 'down');
        list($courseItems, $nextOffsetSeq) = $this->getCourseService()->findCourseItemsByPaging($courseId, array('offsetSeq' => $offsetSeq, 'direction' => $direction));

        return $this->render('task/plugin/list/content.html.twig', array(
            'courseItems' => $courseItems,
            'nextOffsetSeq' => $nextOffsetSeq,
            'course' => $course,
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
            'task' => $task,
        ));
    }

    public function threadsAction(Request $request, $courseId, $taskId)
    {
        list($course, $member) = $this->getCourseService()->tryTakeCourse($courseId);

        $task = $this->getTaskService()->getTask($taskId);

        if (empty($task)) {
            $this->createNewException(TaskException::NOTFOUND_TASK());
        }

        $threads = $this->getThreadService()->searchThreads(
            array(
                'taskId' => $task['id'],
                'type' => 'question',
            ),
            array(
                'createdTime' => 'DESC',
            ),
            0, 20
        );

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($threads, 'userId'));

        $form = $this->createQuestionForm(array(
            'courseId' => $course['id'],
            'taskId' => $taskId,
        ));

        return $this->render('task/plugin/questions.html.twig', array(
            'threads' => $threads,
            'task' => $task,
            'form' => $form->createView(),
            'users' => $users,
        ));
    }

    public function createThreadAction(Request $request, $courseId, $taskId)
    {
        $form = $this->createQuestionForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            $question = $form->getData();
            $question['type'] = 'question';

            $thread = $this->getThreadService()->createThread($question);
            $task = $this->getTaskService()->getTask($taskId);

            return $this->render('task/plugin/question/item.html.twig', array(
                'thread' => $thread,
                'task' => $task,
                'user' => $this->getCurrentUser(),
            ));
        } else {
            return $this->createJsonResponse(false);
        }
    }

    public function threadAction(Request $request, $courseId, $taskId, $threadId)
    {
        list($course, $member) = $this->getCourseService()->tryTakeCourse($courseId);

        $thread = $this->getThreadService()->getThread(
            $course['id'],
            $threadId
        );

        $paginator = new Paginator(
            $request,
            $this->getThreadService()->getThreadPostCount($course['id'], $thread['id']),
            100
        );

        $posts = $this->getThreadService()->findThreadPosts(
            $thread['courseId'],
            $thread['id'],
            'default',
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $threader = $this->getUserService()->getUser($thread['userId']);
        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($posts, 'userId'));

        $form = $this->createPostForm(array(
            'courseId' => $course['id'],
            'threadId' => $thread['id'],
        ));

        $isManager = false;

        return $this->render('task/plugin/question/question.html.twig', array(
            'course' => $course,
            'thread' => $thread,
            'threader' => $threader,
            'posts' => $posts,
            'users' => $users,
            'isManager' => $isManager,
            'form' => $form->createView(),
        ));
    }

    public function answerQuestionAction(Request $request, $courseId, $taskId, $threadId)
    {
        $form = $this->createPostForm();

        $form->handleRequest($request);

        if ($form->isValid()) {
            $post = $form->getData();
            $post = $this->getThreadService()->createPost($post);

            return $this->render('task/plugin/question/post-item.html.twig', array(
                'post' => $post,
                'user' => $this->getUserService()->getUser($post['userId']),
                'course' => $this->getCourseService()->getCourse($post['courseId']),
            ));
        } else {
            return $this->createJsonResponse(false);
        }
    }

    private function createQuestionForm(array $data = array())
    {
        $form = $this->get('form.factory')->createNamedBuilder('question', FormType::class, $data, array());

        return $form
            ->add('title', TextType::class)
            ->add('content', TextareaType::class)
            ->add('courseId', HiddenType::class)
            ->add('taskId', HiddenType::class)
            ->getForm();
    }

    private function createPostForm($data = array())
    {
        return $this->createNamedFormBuilder('post', $data)
            ->add('content', TextareaType::class)
            ->add('courseId', HiddenType::class)
            ->add('threadId', HiddenType::class)
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
        return $this->createService('Course:CourseNoteService');
    }

    /**
     * @return ThreadService
     */
    protected function getThreadService()
    {
        return $this->createService('Course:ThreadService');
    }
}
