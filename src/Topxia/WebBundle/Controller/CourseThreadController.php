<?php
namespace Topxia\WebBundle\Controller;

use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;

class CourseThreadController extends CourseBaseController
{
    public function indexAction(Request $request, $id)
    {
        list($course, $member, $response) = $this->buildLayoutDataWithTakenAccess($request, $id);

        if ($response) {
            return $response;
        }

        $filters    = $this->getThreadSearchFilters($request);
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

        foreach ($threads as $key => $thread) {
            $threads[$key]['sticky']         = $thread['isStick'];
            $threads[$key]['nice']           = $thread['isElite'];
            $threads[$key]['lastPostTime']   = $thread['latestPostTime'];
            $threads[$key]['lastPostUserId'] = $thread['latestPostUserId'];
        }

        $lessons = $this->getCourseService()->findLessonsByIds(ArrayToolkit::column($threads, 'lessonId'));
        $userIds = array_merge(
            ArrayToolkit::column($threads, 'userId'),
            ArrayToolkit::column($threads, 'latestPostUserId')
        );
        $users = $this->getUserService()->findUsersByIds($userIds);

        return $this->render("TopxiaWebBundle:CourseThread:index.html.twig", array(
            'course'    => $course,
            'member'    => $member,
            'threads'   => $threads,
            'users'     => $users,
            'paginator' => $paginator,
            'filters'   => $filters,
            'lessons'   => $lessons,
            'target'    => array('type' => 'course', 'id' => $id)
        ));
    }

    public function showAction(Request $request, $courseId, $threadId)
    {
        list($course, $member, $response) = $this->buildLayoutDataWithTakenAccess($request, $courseId);

        if ($response) {
            return $response;
        }

        if ($course['parentId']) {
            $classroom        = $this->getClassroomService()->findClassroomByCourseId($course['id']);
            $classroomSetting = $this->getSettingService()->get('classroom');
            if (!$this->getClassroomService()->canLookClassroom($classroom['classroomId'])) {
                return $this->createMessageResponse('info', "非常抱歉，您无权限访问该{$classroomSetting['name']}，如有需要请联系客服", '', 3, $this->generateUrl('homepage'));
            }
        }

        $user = $this->getCurrentUser();

        if ($member && !$this->getCourseService()->isMemberNonExpired($course, $member)) {
            $isMemberNonExpired = false;
        } else {
            $isMemberNonExpired = true;
        }

        $thread = $this->getThreadService()->getThread($course['id'], $threadId);

        if (empty($thread)) {
            throw $this->createNotFoundException("话题不存在，或已删除。");
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

        if ($thread['type'] == 'question' && $paginator->getCurrentPage() == 1) {
            $elitePosts = $this->getThreadService()->findThreadElitePosts($thread['courseId'], $thread['id'], 0, 10);
        } else {
            $elitePosts = array();
        }

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($posts, 'userId'));

        $this->getThreadService()->hitThread($courseId, $threadId);

        $isManager = $this->getCourseService()->canManageCourse($course['id']);

        $lesson = $this->getCourseService()->getCourseLesson($course['id'], $thread['lessonId']);
        return $this->render("TopxiaWebBundle:CourseThread:show.html.twig", array(
            'course'             => $course,
            'member'             => $member,
            'lesson'             => $lesson,
            'thread'             => $thread,
            'author'             => $this->getUserService()->getUser($thread['userId']),
            'posts'              => $posts,
            'elitePosts'         => $elitePosts,
            'users'              => $users,
            'isManager'          => $isManager,
            'isMemberNonExpired' => $isMemberNonExpired,
            'paginator'          => $paginator
        ));
    }

    public function createAction(Request $request, $id)
    {
        list($course, $member, $response) = $this->buildLayoutDataWithTakenAccess($request, $id);

        if ($response) {
            return $response;
        }

        if ($member && !$this->getCourseService()->isMemberNonExpired($course, $member)) {
            return $this->redirect($this->generateUrl('course_threads', array('id' => $id)));
        }

        if ($member && $member['levelId'] > 0) {
            if ($this->getVipService()->checkUserInMemberLevel($member['userId'], $course['vipLevelId']) != 'ok') {
                return $this->redirect($this->generateUrl('course_show', array('id' => $id)));
            }
        }

        $type = $request->query->get('type') ?: 'discussion';
        $form = $this->createThreadForm(array(
            'type'     => $type,
            'courseId' => $course['id']
        ));

        if ($request->getMethod() == 'POST') {
            $form->bind($request);
            $formData = $request->request->all();
            if ($form->isValid()) {
                try {
                    $thread     = $this->getThreadService()->createThread($form->getData());
                    $attachment = $request->request->get('attachment');
                    $this->getUploadFileService()->createUseFiles($attachment['fileIds'], $thread['id'], $attachment['targetType'], $attachment['type']);

                    return $this->redirect($this->generateUrl('course_thread_show', array(
                        'courseId' => $thread['courseId'],
                        'threadId' => $thread['id']
                    )));
                } catch (\Exception $e) {
                    return $this->createMessageResponse('error', $e->getMessage(), '错误提示', 1, $request->getPathInfo());
                }
            }
        }

        return $this->render("TopxiaWebBundle:CourseThread:form.html.twig", array(
            'course' => $course,
            'member' => $member,
            'form'   => $form->createView(),
            'type'   => $type
        ));
    }

    public function editAction(Request $request, $courseId, $id)
    {
        list($course, $member, $response) = $this->buildLayoutDataWithTakenAccess($request, $courseId);

        if ($response) {
            return $response;
        }

        $thread = $this->getThreadService()->getThread($courseId, $id);

        if (empty($thread)) {
            throw $this->createNotFoundException();
        }

        $user = $this->getCurrentUser();

        if ($user->isLogin() && $user->id == $thread['userId']) {
            $course = $this->getCourseService()->getCourse($courseId);
        } else {
            $course = $this->getCourseService()->tryManageCourse($courseId);
        }

        $form = $this->createThreadForm($thread);

        if ($request->getMethod() == 'POST') {
            try {
                $form->bind($request);
                $formData = $request->request->all();

                if ($form->isValid()) {
                    $thread     = $this->getThreadService()->updateThread($thread['courseId'], $thread['id'], $form->getData());
                    $attachment = $request->request->get('attachment');
                    $this->getUploadFileService()->createUseFiles($attachment['fileIds'], $thread['id'], $attachment['targetType'], $attachment['type']);

                    if ($user->isAdmin()) {
                        $threadUrl = $this->generateUrl('course_thread_show', array('courseId' => $courseId, 'threadId' => $thread['id']), true);
                        $message   = array(
                            'courseId'   => $courseId,
                            'id'         => $thread['id'],
                            'title'      => $thread['title'],
                            'threadType' => $thread['type'],
                            'type'       => 'modify'
                        );

                        $this->getNotifiactionService()->notify($thread['userId'], 'course-thread', $message);
                    }

                    return $this->redirect($this->generateUrl('course_thread_show', array(
                        'courseId' => $thread['courseId'],
                        'threadId' => $thread['id']
                    )));
                }
            } catch (\Exception $e) {
                return $this->createMessageResponse('error', $e->getMessage(), '错误提示', 1, $request->getPathInfo());
            }
        }

        return $this->render("TopxiaWebBundle:CourseThread:form.html.twig", array(
            'form'   => $form->createView(),
            'course' => $course,
            'member' => $member,
            'thread' => $thread,
            'type'   => $thread['type']
        ));
    }

    protected function createThreadForm($data = array())
    {
        return $this->createNamedFormBuilder('thread', $data)
            ->add('title', 'text')
            ->add('content', 'textarea')
            ->add('type', 'hidden')
            ->add('courseId', 'hidden')
            ->getForm();
    }

    public function deleteAction(Request $request, $courseId, $id)
    {
        $thread = $this->getThreadService()->getThread($courseId, $id);
        $this->getThreadService()->deleteThread($id);
        $user = $this->getCurrentUser();

        if ($user->isAdmin()) {
            $threadUrl = $this->generateUrl('course_thread_show', array('courseId' => $courseId, 'threadId' => $id), true);
            $message   = array(
                'courseId'   => $courseId,
                'id'         => $id,
                'title'      => $thread['title'],
                'threadType' => $thread['type'],
                'type'       => 'delete'
            );

            $this->getNotifiactionService()->notify($thread['userId'], 'course-thread', $message);
        }

        return $this->createJsonResponse(true);
    }

    public function stickAction(Request $request, $courseId, $id)
    {
        $thread = $this->getThreadService()->getThread($courseId, $id);
        $this->getThreadService()->stickThread($courseId, $id);
        $user = $this->getCurrentUser();

        if ($user->isAdmin()) {
            $message = array(
                'courseId'   => $courseId,
                'id'         => $id,
                'title'      => $thread['title'],
                'threadType' => $thread['type'],
                'type'       => 'top'
            );

            $this->getNotifiactionService()->notify($thread['userId'], 'course-thread', $message);
        }

        return $this->createJsonResponse(true);
    }

    public function unstickAction(Request $request, $courseId, $id)
    {
        $thread = $this->getThreadService()->getThread($courseId, $id);
        $this->getThreadService()->unstickThread($courseId, $id);
        $user = $this->getCurrentUser();

        if ($user->isAdmin()) {
            $message = array(
                'courseId'   => $courseId,
                'id'         => $id,
                'title'      => $thread['title'],
                'threadType' => $thread['type'],
                'type'       => 'untop'
            );
            $this->getNotifiactionService()->notify($thread['userId'], 'course-thread', $message);
        }

        return $this->createJsonResponse(true);
    }

    public function eliteAction(Request $request, $courseId, $id)
    {
        $thread = $this->getThreadService()->getThread($courseId, $id);
        $this->getThreadService()->eliteThread($courseId, $id);
        $user = $this->getCurrentUser();

        if ($user->isAdmin()) {
            $message = array(
                'courseId'   => $courseId,
                'id'         => $id,
                'title'      => $thread['title'],
                'threadType' => $thread['type'],
                'type'       => 'elite'
            );
            $threadUrl = $this->generateUrl('course_thread_show', array('courseId' => $courseId, 'threadId' => $id), true);
            $this->getNotifiactionService()->notify($thread['userId'], 'course-thread', $message);
        }

        return $this->createJsonResponse(true);
    }

    public function uneliteAction(Request $request, $courseId, $id)
    {
        $thread = $this->getThreadService()->getThread($courseId, $id);
        $this->getThreadService()->uneliteThread($courseId, $id);
        $user = $this->getCurrentUser();

        if ($user->isAdmin()) {
            $message = array(
                'courseId'   => $courseId,
                'id'         => $id,
                'title'      => $thread['title'],
                'threadType' => $thread['type'],
                'type'       => 'unelite'
            );
            $threadUrl = $this->generateUrl('course_thread_show', array('courseId' => $courseId, 'threadId' => $id), true);
            $this->getNotifiactionService()->notify($thread['userId'], 'course-thread', $message);
        }

        return $this->createJsonResponse(true);
    }

    public function postAction(Request $request, $courseId, $id)
    {
        list($course, $member) = $this->getCourseService()->tryTakeCourse($courseId);

        if ($course['parentId']) {
            $classroom        = $this->getClassroomService()->findClassroomByCourseId($course['id']);
            $classroomSetting = $this->getSettingService()->get('classroom');
            if (!$this->getClassroomService()->canLookClassroom($classroom['classroomId'])) {
                return $this->createMessageResponse('info', "非常抱歉，您无权限访问该{$classroomSetting['name']}，如有需要请联系客服", '', 3, $this->generateUrl('homepage'));
            }
        }

        $thread = $this->getThreadService()->getThread($course['id'], $id);
        $form   = $this->createPostForm(array(
            'courseId' => $thread['courseId'],
            'threadId' => $thread['id']
        ));
        $currentUser = $this->getCurrentUser();

        if ($request->getMethod() == 'POST') {
            $form->bind($request);
            $userId = $currentUser->id;

            if ($form->isValid()) {
                $formData = $request->request->all();
                $postData = $form->getData();

                list($postData, $users) = $this->replaceMention($postData);

                $post = $this->getThreadService()->createPost($postData);

                $attachment = $request->request->get('attachment');
                $this->getUploadFileService()->createUseFiles($attachment['fileIds'], $post['id'], $attachment['targetType'], $attachment['type']);

                $threadUrl = $this->generateUrl('course_thread_show', array('courseId' => $courseId, 'threadId' => $id), true);
                $threadUrl .= "#post-".$post['id'];

                if ($thread['userId'] != $currentUser->id) {
                    $message = array(
                        'userId'     => $currentUser['id'],
                        'userName'   => $currentUser['nickname'],
                        'courseId'   => $courseId,
                        'id'         => $id,
                        'title'      => $thread['title'],
                        'threadType' => $thread['type'],
                        'postId'     => $post['id'],
                        'type'       => 'reply'

                    );
                    $this->getNotifiactionService()->notify($thread['userId'], 'course-thread', $message);
                }

                foreach ($users as $user) {
                    if ($thread['userId'] != $user['id']) {
                        if ($user['id'] != $userId) {
                            $message = array(
                                'userId'     => $currentUser['id'],
                                'userName'   => $currentUser['nickname'],
                                'courseId'   => $courseId,
                                'id'         => $id,
                                'title'      => $thread['title'],
                                'threadType' => $thread['type'],
                                'postId'     => $post['id'],
                                'type'       => 'replayat'
                            );

                            $this->getNotifiactionService()->notify($user['id'], 'course-thread', $message);
                        }
                    }
                }

                return $this->render('TopxiaWebBundle:CourseThread:post-list-item.html.twig', array(
                    'course'    => $course,
                    'thread'    => $thread,
                    'post'      => $post,
                    'author'    => $this->getUserService()->getUser($post['userId']),
                    'isManager' => $this->getCourseService()->canManageCourse($course['id'])
                ));
            } else {
                return $this->createJsonResponse(false);
            }
        }

        return $this->render('TopxiaWebBundle:CourseThread:post.html.twig', array(
            'course' => $course,
            'member' => $member,
            'thread' => $thread,
            'form'   => $form->createView()
        ));
    }

    protected function replaceMention($postData)
    {
        $currentUser = $this->getCurrentUser();
        $content     = $postData['content'];
        $users       = array();
        preg_match_all('/@([\x{4e00}-\x{9fa5}\w]{2,16})/u', $content, $matches);
        $mentions = array_unique($matches[1]);

        foreach ($mentions as $mention) {
            $user = $this->getUserService()->getUserByNickname($mention);

            if ($user) {
                $path     = $this->generateUrl('user_show', array('id' => $user['id']));
                $nickname = $user['nickname'];
                $html     = "<a href=\"{$path}\" class=\"show-user\">@{$nickname}</a>";

                $content = preg_replace("/@{$nickname}/ui", $html, $content);

                $users[] = $user;
            }
        }

        $postData['content'] = $content;

        return array($postData, $users);
    }

    public function editPostAction(Request $request, $courseId, $threadId, $id)
    {
        list($course, $member, $response) = $this->buildLayoutDataWithTakenAccess($request, $courseId);

        if ($response) {
            return $response;
        }

        $post = $this->getThreadService()->getPost($courseId, $id);

        if (empty($post)) {
            throw $this->createNotFoundException();
        }

        $user = $this->getCurrentUser();

        if ($user->isLogin() && $user->id == $post['userId']) {
            $course = $this->getCourseService()->getCourse($courseId);
        } else {
            $course = $this->getCourseService()->tryManageCourse($courseId);
        }

        $thread = $this->getThreadService()->getThread($courseId, $threadId);

        $form = $this->createPostForm($post);

        if ($request->getMethod() == 'POST') {
            $formData = $request->request->all();
            $form->bind($request);

            if ($form->isValid()) {
                $post = $this->getThreadService()->updatePost($post['courseId'], $post['id'], $form->getData());

                $attachment = $request->request->get('attachment');
                $this->getUploadFileService()->createUseFiles($attachment['fileIds'], $post['id'], $attachment['targetType'], $attachment['type']);
                if ($user->isAdmin()) {
                    $message = array(
                        'userId'     => $user['id'],
                        'userName'   => $user['nickname'],
                        'courseId'   => $courseId,
                        'id'         => $threadId,
                        'threadType' => $thread['type'],
                        'title'      => $thread['title'],
                        'postId'     => $post['id']
                    );
                    $message['type'] = 'modify-thread';
                    $this->getNotifiactionService()->notify($thread['userId'], 'course-thread', $message);
                    $message['type'] = 'modify-post';
                    $this->getNotifiactionService()->notify($post['userId'], 'course-thread', $message);
                }

                return $this->redirect($this->generateUrl('course_thread_show', array(
                    'courseId' => $post['courseId'],
                    'threadId' => $post['threadId']
                )));
            }
        }

        return $this->render('TopxiaWebBundle:CourseThread:post-form.html.twig', array(
            'course' => $course,
            'member' => $member,
            'form'   => $form->createView(),
            'post'   => $post,
            'thread' => $thread
        ));
    }

    public function deletePostAction(Request $request, $courseId, $threadId, $id)
    {
        $post = $this->getThreadService()->getPost($courseId, $id);
        $this->getThreadService()->deletePost($courseId, $id);
        $user   = $this->getCurrentUser();
        $thread = $this->getThreadService()->getThread($courseId, $threadId);

        if ($user->isAdmin()) {
            $threadUrl = $this->generateUrl('course_thread_show', array('courseId' => $courseId, 'threadId' => $threadId), true);

            $message = array(
                'userId'     => $user['id'],
                'userName'   => $user['nickname'],
                'courseId'   => $courseId,
                'id'         => $threadId,
                'postId'     => $post['id'],
                'threadType' => $thread['type'],
                'title'      => $thread['title'],
                'type'       => 'delete-post'
            );

            $this->getNotifiactionService()->notify($thread['userId'], 'course-thread', $message);
            $this->getNotifiactionService()->notify($post['userId'], 'course-thread', $message);
        }

        return $this->createJsonResponse(true);
    }

    protected function getThreadService()
    {
        return $this->getServiceKernel()->createService('Course.ThreadService');
    }

    protected function getThreadSearchFilters($request)
    {
        $filters         = array();
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

    protected function convertFiltersToConditions($course, $filters)
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

    protected function getNotifiactionService()
    {
        return $this->getServiceKernel()->createService('User.NotificationService');
    }

    protected function getVipService()
    {
        return $this->getServiceKernel()->createService('Vip:Vip.VipService');
    }

    protected function getClassroomService()
    {
        return $this->getServiceKernel()->createService('Classroom:Classroom.ClassroomService');
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

    protected function getUploadFileService()
    {
        return $this->createService('File.UploadFileService');
    }

    protected function createPostForm($data = array())
    {
        return $this->createNamedFormBuilder('post', $data)
            ->add('content', 'textarea')
            ->add('courseId', 'hidden')
            ->add('threadId', 'hidden')
            ->getForm();
    }
}
