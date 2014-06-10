<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;

class CourseThreadController extends BaseController
{
    public function indexAction(Request $request, $id)
    {
        $user = $this->getCurrentUser();
        if (!$user->isLogin()) {
            return $this->createMessageResponse('info', '你好像忘了登录哦？', null, 3000, $this->generateUrl('login'));
        }

        $course = $this->getCourseService()->getCourse($id);
        if (empty($course)) {
            throw $this->createNotFoundException("课程不存在，或已删除。");
        }

        if (!$this->getCourseService()->canTakeCourse($course)) {
            return $this->createMessageResponse('info', "您还不是课程《{$course['title']}》的学员，请先购买或加入学习。", null, 3000, $this->generateUrl('course_show', array('id' => $id)));
        }

        list($course, $member) = $this->getCourseService()->tryTakeCourse($id);

        $filters = $this->getThreadSearchFilters($request);
        $conditions = $this->convertFiltersToConditions($course, $filters);

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

        $lessons = $this->getCourseService()->findLessonsByIds(ArrayToolkit::column($threads, 'lessonId'));
        $userIds = array_merge(
            ArrayToolkit::column($threads, 'userId'),
            ArrayToolkit::column($threads, 'latestPostUserId')
        );
        $users = $this->getUserService()->findUsersByIds($userIds);

        $template = $request->isXmlHttpRequest() ? 'index-main' : 'index';
        return $this->render("TopxiaWebBundle:CourseThread:{$template}.html.twig", array(
            'course' => $course,
            'threads' => $threads,
            'users' => $users,
            'paginator' => $paginator,
            'filters' => $filters,
            'lessons'=>$lessons
        ));
    }

    public function showAction(Request $request, $courseId, $id)
    {

        $user = $this->getCurrentUser();
        if (!$user->isLogin()) {
            return $this->createMessageResponse('info', '你好像忘了登录哦？', null, 3000, $this->generateUrl('login'));
        }

        $course = $this->getCourseService()->getCourse($courseId);
        if (empty($course)) {
            throw $this->createNotFoundException("课程不存在，或已删除。");
        }

        if (!$this->getCourseService()->canTakeCourse($course)) {
            return $this->createMessageResponse('info', "您还不是课程《{$course['title']}》的学员，请先购买或加入学习。", null, 3000, $this->generateUrl('course_show', array('id' => $courseId)));
        }

        list($course, $member) = $this->getCourseService()->tryTakeCourse($courseId);

        if ($member && !$this->getCourseService()->isMemberNonExpired($course, $member)) {
            // return $this->redirect($this->generateUrl('course_threads',array('id' => $courseId)));
            $isMemberNonExpired = false;
        } else {
            $isMemberNonExpired = true;
        }
        
        $thread = $this->getThreadService()->getThread($course['id'], $id);
        if (empty($thread)) {
            throw $this->createNotFoundException();
        }

        $paginator = new Paginator(
            $request,
            $this->getThreadService()->getThreadPostCount($course['id'], $thread['id']),
            30
        );

        $posts = $this->getThreadService()->findThreadPosts(
            $thread['courseId'],
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

        $this->getThreadService()->hitThread($courseId, $id);

        $isManager = $this->getCourseService()->canManageCourse($course['id']);

        $lesson = $this->getCourseService()->getCourseLesson($course['id'], $thread['lessonId']);
        return $this->render("TopxiaWebBundle:CourseThread:show.html.twig", array(
            'course' => $course,
            'lesson' => $lesson,
            'thread' => $thread,
            'author' => $this->getUserService()->getUser($thread['userId']),
            'posts' => $posts,
            'elitePosts' => $elitePosts,
            'users' => $users,
            'isManager' => $isManager,
            'isMemberNonExpired' => $isMemberNonExpired,
            'paginator' => $paginator,
        ));
    }

    public function createAction(Request $request, $id)
    {
        list($course, $member) = $this->getCourseService()->tryTakeCourse($id);

        if ($member && !$this->getCourseService()->isMemberNonExpired($course, $member)) {
            return $this->redirect($this->generateUrl('course_threads',array('id' => $id)));
        }

        if ($member && $member['levelId'] > 0) {
            if ($this->getVipService()->checkUserInMemberLevel($member['userId'], $course['vipLevelId']) != 'ok') {
                return $this->redirect($this->generateUrl('course_show',array('id' => $id)));
            }
        }


        $type = $request->query->get('type') ? : 'discussion';
        $form = $this->createThreadForm(array(
        	'type' => $type,
        	'courseId' => $course['id'],
    	));

        if ($request->getMethod() == 'POST') {
            $form->bind($request);
            if ($form->isValid()) {
                $thread = $this->getThreadService()->createThread($form->getData());
                return $this->redirect($this->generateUrl('course_thread_show', array(
                   'courseId' => $thread['courseId'],
                   'id' => $thread['id'], 
                )));
            }
        }

        return $this->render("TopxiaWebBundle:CourseThread:form.html.twig", array(
            'course' => $course,
            'form' => $form->createView(),
            'type' => $type,
        ));
    }

    public function editAction(Request $request, $courseId, $id)
    {
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

    public function latestBlockAction($course)
    {
    	$threads = $this->getThreadService()->searchThreads(array('courseId' => $course['id']), 'createdNotStick', 0, 10);

    	return $this->render('TopxiaWebBundle:CourseThread:latest-block.html.twig', array(
    		'course' => $course,
            'threads' => $threads,
		));
    }

    public function deleteAction(Request $request, $courseId, $id)
    {
        $this->getThreadService()->deleteThread($id);
        return $this->createJsonResponse(true);
    }

    public function stickAction(Request $request, $courseId, $id)
    {
        $this->getThreadService()->stickThread($courseId, $id);
        return $this->createJsonResponse(true);
    }

    public function unstickAction(Request $request, $courseId, $id)
    {
        $this->getThreadService()->unstickThread($courseId, $id);
        return $this->createJsonResponse(true);
    }

    public function eliteAction(Request $request, $courseId, $id)
    {
        $this->getThreadService()->eliteThread($courseId, $id);
        return $this->createJsonResponse(true);
    }

    public function uneliteAction(Request $request, $courseId, $id)
    {
        $this->getThreadService()->uneliteThread($courseId, $id);
        return $this->createJsonResponse(true);
    }

    public function postAction(Request $request, $courseId, $id)
    {
        list($course, $member) = $this->getCourseService()->tryTakeCourse($courseId);
        $thread = $this->getThreadService()->getThread($course['id'], $id);
        $form = $this->createPostForm(array(
            'courseId' => $thread['courseId'],
            'threadId' => $thread['id']
        ));

        if ($request->getMethod() == 'POST') {
            $form->bind($request);
            if ($form->isValid()) {
                $post = $this->getThreadService()->createPost($form->getData());

                return $this->render('TopxiaWebBundle:CourseThread:post-list-item.html.twig', array(
                    'course' => $course,
                    'thread' => $thread,
                    'post' => $post,
                    'author' => $this->getUserService()->getUser($post['userId']),
                    'isManager' => $this->getCourseService()->canManageCourse($course['id'])
                ));
            } else {
                return $this->createJsonResponse(false);
            }
        }

        return $this->render('TopxiaWebBundle:CourseThread:post.html.twig', array(
            'course' => $course,
            'thread' => $thread,
            'form' => $form->createView()
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

    public function questionBlockAction(Request $request, $course)
    {
        $threads = $this->getThreadService()->searchThreads(
            array('courseId' => $course['id'], 'type'=> 'question'),
            'createdNotStick',
            0,
            8
        );

        return $this->render('TopxiaWebBundle:CourseThread:question-block.html.twig', array(
            'course' => $course,
            'threads' => $threads,
        ));
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
        return $this->getServiceKernel()->createService('Course.ThreadService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    private function getThreadSearchFilters($request)
    {
        $filters = array();
        $filters['type'] = $request->query->get('type');
        if (!in_array($filters['type'], array('all', 'question', 'elite'))) {
            $filters['type'] = 'all';
        }
        $filters['sort'] = $request->query->get('sort');

        if (!in_array($filters['sort'], array('created', 'posted', 'createdNotStick', 'postedNotStick'))) {
            $filters['sort'] = 'posted';
        }
        return $filters;
    }

    private function convertFiltersToConditions($course, $filters)
    {
        $conditions = array('courseId' => $course['id']);
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

    protected function getVipService()
    {
        return $this->getServiceKernel()->createService('Vip:Vip.VipService');
    } 
}