<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;

class ClassThreadController extends ClassBaseController
{
    public function listAction(Request $request, $classId)
    {
        $class = $this->tryViewClass($classId);

        $filters = $this->getThreadSearchFilters($request);
        $conditions = $this->convertFiltersToConditions($class, $filters);

        $paginator = new Paginator(
            $request,
            $this->getThreadService()->searchThreadCount($conditions),
            20
        );

        $threads = $this->getThreadService()->searchThreads(
            $conditions,
            $filters['sort'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $userIds = array_merge(
            ArrayToolkit::column($threads, 'userId'),
            ArrayToolkit::column($threads, 'latestPostUserId')
        );
        $users = $this->getUserService()->findUsersByIds($userIds);

        return $this->render("TopxiaWebBundle:ClassThread:list.html.twig", array(
            'class' => $class,
            'threads' => $threads,
            'users' => $users,
            'paginator' => $paginator,
            'filters' => $filters,
        ));
    }

    public function showAction(Request $request, $classId, $threadId)
    {
        $class = $this->tryViewClass($classId);

        $thread = $this->getThreadService()->getThread($threadId);
        if (empty($thread)) {
            throw $this->createNotFoundException();
        }

        $paginator = new Paginator(
            $request,
            $this->getThreadService()->getThreadPostCount($thread['id']),
            30
        );

        $posts = $this->getThreadService()->findThreadPosts(
            $thread['id'],
            'default',
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        if ($thread['type'] == 'question' and $paginator->getCurrentPage() == 1) {
            $elitePosts = $this->getThreadService()->findThreadElitePosts($thread['courseId'], $thread['id'], 0, 10);
        } else {
            $elitePosts = array();
        }

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($posts, 'userId'));

        $this->getThreadService()->hitThread($threadId);

        return $this->render("TopxiaWebBundle:ClassThread:show.html.twig", array(
            'class' => $class,
            'thread' => $thread,
            'author' => $this->getUserService()->getUser($thread['userId']),
            'posts' => $posts,
            'elitePosts' => $elitePosts,
            'users' => $users,
            'isManager' => true,    // todo
            'paginator' => $paginator,
        ));
    }

    public function createAction(Request $request, $classId)
    {
        $class = $this->tryViewClass($classId);

        $type = $request->query->get('type') ? : 'discussion';

        if ($request->getMethod() == 'POST') {

            $thread = $request->request->all();
            $thread['classId'] = $class['id'];
            $thread['type'] = 'discussion';

            $thread = $this->getThreadService()->createThread($thread);
            return $this->redirect($this->generateUrl('class_thread_show', array(
               'classId' => $thread['classId'],
               'threadId' => $thread['id'], 
            )));
        }

        return $this->render("TopxiaWebBundle:ClassThread:thread-form.html.twig", array(
            'class' => $class,
            'type' => $type,
        ));
    }

    public function editAction(Request $request, $courseId, $id)
    {
        $class = $this->tryViewClass($classId);
        
        $thread = $this->getThreadService()->getThread($courseId, $id);
        if (empty($thread)) {
            throw $this->createNotFoundException();
        }

        $user = $this->getCurrentUser();
        if ($user->isLogin() and $user->id == $thread['userId']) {
            $course = $this->getCourseService()->getCourse($courseId);
        } else {
            $course = $this->getCourseService()->tryManageCourse($courseId);
        }

        $form = $this->createThreadForm($thread);
        if ($request->getMethod() == 'POST') {
            $form->bind($request);
            if ($form->isValid()) {
                $thread = $this->getThreadService()->updateThread($thread['courseId'], $thread['id'], $form->getData());
                return $this->redirect($this->generateUrl('course_thread_show', array(
                   'courseId' => $thread['courseId'],
                   'id' => $thread['id'], 
                )));
            }
        }

        return $this->render("TopxiaWebBundle:CourseThread:form.html.twig", array(
            'form' => $form->createView(),
            'course' => $course,
            'thread' => $thread,
            'type' => $thread['type'],
        ));

    }

    private function createThreadForm($data = array())
    {
        return $this->createNamedFormBuilder('thread', $data)
            ->add('title', 'text')
            ->add('content', 'textarea')
            ->add('type', 'hidden')
            ->add('courseId', 'hidden')
            ->getForm();
    }

    public function deleteAction(Request $request, $classId, $threadId)
    {
        $this->getThreadService()->deleteThread($threadId);
        return $this->createJsonResponse(true);
    }

    public function stickAction(Request $request, $classId, $threadId)
    {
        $this->getThreadService()->stickThread($threadId);
        return $this->createJsonResponse(true);
    }

    public function unstickAction(Request $request, $classId, $threadId)
    {
        $this->getThreadService()->unstickThread($threadId);
        return $this->createJsonResponse(true);
    }

    public function eliteAction(Request $request, $classId, $threadId)
    {
        $this->getThreadService()->eliteThread($threadId);
        return $this->createJsonResponse(true);
    }

    public function uneliteAction(Request $request, $classId, $threadId)
    {
        $this->getThreadService()->uneliteThread($threadId);
        return $this->createJsonResponse(true);
    }

    public function postAction(Request $request, $classId, $threadId)
    {
        $class = $this->getClassService()->getClass($classId);
        $thread = $this->getThreadService()->getThread($threadId);

        if ($request->getMethod() == 'POST') {
            $post = $request->request->all();

            $post['classId'] = $thread['classId'];
            $post['threadId'] = $thread['id'];

            $post = $this->getThreadService()->createPost($post);

            return $this->render('TopxiaWebBundle:ClassThread:post-list-item.html.twig', array(
                'class' => $class,
                'thread' => $thread,
                'post' => $post,
                'author' => $this->getUserService()->getUser($post['userId']),
                'isManager' => true // @todo
            ));

        }

        return $this->render('TopxiaWebBundle:ClassThread:post-form.html.twig', array(
            'class' => $class,
            'thread' => $thread,
        ));
    }

    public function editPostAction(Request $request, $courseId, $threadId, $id)
    {
        $post = $this->getThreadService()->getPost($courseId, $id);
        if (empty($post)) {
            throw $this->createNotFoundException();
        }

        $user = $this->getCurrentUser();
        if ($user->isLogin() and $user->id == $post['userId']) {
            $course = $this->getCourseService()->getCourse($courseId);
        } else {
            $course = $this->getCourseService()->tryManageCourse($courseId);
        }

        $thread = $this->getThreadService()->getThread($courseId, $threadId);

        $form = $this->createPostForm($post);

        if ($request->getMethod() == 'POST') {
            $form->bind($request);
            if ($form->isValid()) {
                $post = $this->getThreadService()->updatePost($post['courseId'], $post['id'], $form->getData());
                return $this->redirect($this->generateUrl('course_thread_show', array(
                    'courseId' => $post['courseId'],
                    'id' => $post['threadId']
                )));
            }
        }

        return $this->render('TopxiaWebBundle:CourseThread:post-form.html.twig', array(
            'course' => $course,
            'form' => $form->createView(),
            'post' => $post,
            'thread' => $thread,
        ));

    }

    public function deletePostAction(Request $request, $courseId, $threadId, $id)
    {
        $this->getThreadService()->deletePost($courseId, $id);
        return $this->createJsonResponse(true);
    }

    private function createPostForm($data = array())
    {
        return $this->createNamedFormBuilder('post', $data)
            ->add('content', 'textarea')
            ->add('courseId', 'hidden')
            ->add('threadId', 'hidden')
            ->getForm();
    }

    protected function getThreadService()
    {
        return $this->getServiceKernel()->createService('Classes.ThreadService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    protected function getClassService()
    {
        return $this->getServiceKernel()->createService('Classes.ClassesService');
    }

    private function getThreadSearchFilters($request)
    {
        $filters = array();
        $filters['type'] = $request->query->get('type');
        if (!in_array($filters['type'], array('all', 'elite'))) {
            $filters['type'] = 'all';
        }
        $filters['sort'] = $request->query->get('sort');

        if (!in_array($filters['sort'], array('created', 'posted', 'createdNotStick', 'postedNotStick'))) {
            $filters['sort'] = 'posted';
        }
        return $filters;
    }

    private function convertFiltersToConditions($class, $filters)
    {
        $conditions = array('classId' => $class['id']);
        switch ($filters['type']) {
            case 'question':
                $conditions['type'] = 'question';
                break;
            case 'elite':
                $conditions['isElite'] = 1;
                break;
            default:
                break;
        }
        return $conditions;
    }
}