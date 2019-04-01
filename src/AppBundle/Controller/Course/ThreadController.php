<?php

namespace AppBundle\Controller\Course;

use AppBundle\Common\Paginator;
use Biz\Course\MemberException;
use Biz\Task\Service\TaskService;
use AppBundle\Common\ArrayToolkit;
use Biz\Thread\ThreadException;
use Biz\Course\Service\ThreadService;
use Biz\System\Service\SettingService;
use Biz\File\Service\UploadFileService;
use Biz\User\Service\NotificationService;
use Biz\Classroom\Service\ClassroomService;
use Symfony\Component\HttpFoundation\Request;
use VipPlugin\Biz\Vip\Service\VipService;

class ThreadController extends CourseBaseController
{
    public function indexAction(Request $request, $course, $member = array())
    {
        $courseMember = $this->getCourseMember($request, $course);
        if (empty($courseMember)) {
            $this->createNewException(MemberException::NOTFOUND_MEMBER());
        }
        $courseSet = $this->getCourseSetService()->getCourseSet($course['courseSetId']);

        $filters = $this->getThreadSearchFilters($request);
        $conditions = $this->convertFiltersToConditions($course, $filters);

        $paginator = new Paginator(
            $request,
            $this->getThreadService()->countThreads($conditions),
            20
        );

        $threads = $this->getThreadService()->searchThreads(
            $conditions,
            $filters['sort'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        foreach ($threads as $key => $thread) {
            $threads[$key]['sticky'] = $thread['isStick'];
            $threads[$key]['nice'] = $thread['isElite'];
            $threads[$key]['lastPostTime'] = $thread['latestPostTime'];
            $threads[$key]['lastPostUserId'] = $thread['latestPostUserId'];
        }
        $tasks = $this->getTaskService()->findTasksByIds(ArrayToolkit::column($threads, 'taskId'));
        $userIds = array_merge(
            ArrayToolkit::column($threads, 'userId'),
            ArrayToolkit::column($threads, 'latestPostUserId')
        );
        $users = $this->getUserService()->findUsersByIds($userIds);

        return $this->render('course/tabs/threads.html.twig', array(
            'type' => $conditions['type'],
            'courseSet' => $courseSet,
            'course' => $course,
            'member' => $member,
            'threads' => $threads,
            'users' => $users,
            'paginator' => $paginator,
            'filters' => $filters,
            'tasks' => $tasks,
            'target' => array('type' => 'course', 'id' => $course['id']),
        ));
    }

    public function showAction(Request $request, $courseId, $threadId)
    {
        list($course, $member, $response) = $this->tryBuildCourseLayoutData($request, $courseId);

        if (!empty($response)) {
            return $response;
        }

        if ($course['parentId']) {
            $classroom = $this->getClassroomService()->getClassroomByCourseId($course['id']);
            $classroomSetting = $this->getSettingService()->get('classroom');
            if (!$this->getClassroomService()->canLookClassroom($classroom['id'])) {
                return $this->createMessageResponse('info', '非常抱歉，您无权限访问该'.$classroomSetting['name'].'，如有需要请联系客服', '', 3, $this->generateUrl('homepage'));
            }
        }

        $user = $this->getCurrentUser();

        $isMemberNonExpired = true;
        if ($member && !$this->getMemberService()->isMemberNonExpired($course, $member)) {
            $isMemberNonExpired = false;
        } else {
            $isMemberNonExpired = true;
        }

        $thread = $this->getThreadService()->getThread($course['id'], $threadId);

        if (empty($thread)) {
            $this->createNewException(ThreadException::NOTFOUND_THREAD());
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

        if ('question' == $thread['type'] && 1 == $paginator->getCurrentPage()) {
            $elitePosts = $this->getThreadService()->findThreadElitePosts($thread['courseId'], $thread['id'], 0, 10);
        } else {
            $elitePosts = array();
        }

        if ('question' == $thread['type'] && $user['id'] != $thread['userId'] && !$this->getMemberService()->isCourseTeacher($course['id'], $user['id'])) {
            $canPost = false;
        } else {
            $canPost = true;
        }

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($posts, 'userId'));

        $this->getThreadService()->hitThread($courseId, $threadId);

        //TODO 先注释掉旧的判断逻辑
        // $isManager = $this->getCourseService()->hasCourseManagerRole($course['id'], 'admin_course_thread');
        $isManager = $this->getCourseService()->hasCourseManagerRole($course['id']);

        $task = $this->getTaskService()->getTask($thread['taskId']);

        return $this->render('course/thread/show.html.twig', array(
            'course' => $course,
            'member' => $member,
            'task' => $task,
            'thread' => $thread,
            'author' => $this->getUserService()->getUser($thread['userId']),
            'posts' => $posts,
            'elitePosts' => $elitePosts,
            'users' => $users,
            'isManager' => $isManager,
            'isMemberNonExpired' => $isMemberNonExpired,
            'canPost' => $canPost,
            'paginator' => $paginator,
        ));
    }

    public function askVideoAction(Request $request, $threadId)
    {
        $thread = $this->getThreadService()->getThread(null, $threadId);
        $user = $this->getCurrentUser();

        return $this->render('course/thread/preview-modal.html.twig', array(
            'courseId' => $thread['courseId'],
            'taskId' => $thread['taskId'],
            'videoAskTime' => $thread['videoAskTime'],
            'fileId' => $thread['videoId'],
            'userId' => $user['id'],
        ));
    }

    public function playerShowAction(Request $request, $id)
    {
        return $this->forward('AppBundle:Player:show', array(
            'id' => $id,
            'remeberLastPos' => false,
        ));
    }

    public function createAction(Request $request, $courseId)
    {
        list($course, $member, $response) = $this->tryBuildCourseLayoutData($request, $courseId);

        if ($response) {
            return $response;
        }

        if ($member && !$this->getMemberService()->isMemberNonExpired($course, $member)) {
            return $this->redirect($this->generateUrl('my_course_show', array('id' => $courseId, 'tab' => 'threads')));
        }

        if ($member && $member['levelId'] > 0) {
            if (empty($course['vipLevelId'])) {
                return $this->redirect($this->generateUrl('course_show', array('id' => $course['id'])));
            } elseif (empty($course['parentId'])
                && $this->isVipPluginEnabled()
                && 'ok' != $this->getVipService()->checkUserInMemberLevel($member['userId'], $course['vipLevelId'])
            ) {
                return $this->redirect($this->generateUrl('course_show', array('id' => $course['id'])));
            } elseif (!empty($course['parentId'])) {
                $classroom = $this->getClassroomService()->getClassroomByCourseId($course['id']);
                if (!empty($classroom)
                    && $this->isVipPluginEnabled()
                    && 'ok' != $this->getVipService()->checkUserInMemberLevel($member['userId'], $classroom['vipLevelId'])
                ) {
                    return $this->redirect($this->generateUrl('course_show', array('id' => $course['id'])));
                }
            }
        }

        $type = $request->query->get('type') ?: 'discussion';
        $form = $this->createThreadForm(array(
            'type' => $type,
            'courseId' => $course['id'],
            'courseSetId' => $course['courseSetId'],
        ));

        if ('POST' == $request->getMethod()) {
            $form->submit($request);
            $formData = $request->request->all();
            if ($form->isValid()) {
                try {
                    $thread = $this->getThreadService()->createThread($form->getData());
                    $attachment = $request->request->get('attachment');
                    $this->getUploadFileService()->createUseFiles($attachment['fileIds'], $thread['id'], $attachment['targetType'], $attachment['type']);

                    return $this->redirect($this->generateUrl('course_thread_show', array(
                        'courseId' => $thread['courseId'],
                        'threadId' => $thread['id'],
                    )));
                } catch (\Exception $e) {
                    return $this->createMessageResponse('error', $this->trans($e->getMessage()), '错误提示', 1, $request->getPathInfo());
                }
            }
        }

        return $this->render('course/thread/form.html.twig', array(
            'course' => $course,
            'member' => $member,
            'form' => $form->createView(),
            'type' => $type,
        ));
    }

    protected function isVipPluginEnabled()
    {
        return $this->isPluginInstalled('Vip') && $this->setting('vip.enabled');
    }

    public function editAction(Request $request, $courseId, $threadId)
    {
        list($course, $member, $response) = $this->tryBuildCourseLayoutData($request, $courseId);

        if ($response) {
            return $response;
        }

        $thread = $this->getThreadService()->getThread($courseId, $threadId);

        if (empty($thread)) {
            $this->createNewException(ThreadException::NOTFOUND_THREAD());
        }

        $user = $this->getCurrentUser();

        if ($user->isLogin() && $user->id == $thread['userId']) {
            $course = $this->getCourseService()->getCourse($courseId);
        } else {
            // $course = $this->getCourseService()->tryManageCourse($courseId, 'admin_course_thread');
            $course = $this->getCourseService()->tryManageCourse($courseId);
        }

        $form = $this->createThreadForm($thread);

        if ('POST' == $request->getMethod()) {
            try {
                $form->submit($request);
                $formData = $request->request->all();

                if ($form->isValid()) {
                    $thread = $this->getThreadService()->updateThread($thread['courseId'], $thread['id'], $form->getData());
                    $attachment = $request->request->get('attachment');
                    $this->getUploadFileService()->createUseFiles($attachment['fileIds'], $thread['id'], $attachment['targetType'], $attachment['type']);

                    if ($user->isAdmin()) {
                        $threadUrl = $this->generateUrl('course_thread_show', array('courseId' => $courseId, 'threadId' => $thread['id']), true);
                        $message = array(
                            'courseId' => $courseId,
                            'id' => $thread['id'],
                            'title' => $thread['title'],
                            'threadType' => $thread['type'],
                            'type' => 'modify',
                        );

                        $this->getNotificationService()->notify($thread['userId'], 'course-thread', $message);
                    }

                    return $this->redirect($this->generateUrl('course_thread_show', array(
                        'courseId' => $thread['courseId'],
                        'threadId' => $thread['id'],
                    )));
                }
            } catch (\Exception $e) {
                return $this->createMessageResponse('error', $this->trans($e->getMessage()), '错误提示', 1, $request->getPathInfo());
            }
        }

        return $this->render('course/thread/form.html.twig', array(
            'form' => $form->createView(),
            'course' => $course,
            'member' => $member,
            'thread' => $thread,
            'type' => $thread['type'],
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

    public function deleteAction(Request $request, $courseId, $threadId)
    {
        $thread = $this->getThreadService()->getThread($courseId, $threadId);
        $this->getThreadService()->deleteThread($thread['id']);
        $user = $this->getCurrentUser();

        if ($user->isAdmin()) {
            $threadUrl = $this->generateUrl('course_thread_show', array('courseId' => $courseId, 'threadId' => $threadId), true);
            $message = array(
                'courseId' => $courseId,
                'id' => $threadId,
                'title' => $thread['title'],
                'threadType' => $thread['type'],
                'type' => 'delete',
            );

            $this->getNotificationService()->notify($thread['userId'], 'course-thread', $message);
        }

        return $this->createJsonResponse(true);
    }

    public function stickAction(Request $request, $courseId, $threadId)
    {
        $thread = $this->getThreadService()->getThread($courseId, $threadId);
        $this->getThreadService()->stickThread($courseId, $threadId);
        $user = $this->getCurrentUser();

        if ($user->isAdmin()) {
            $message = array(
                'courseId' => $courseId,
                'id' => $threadId,
                'title' => $thread['title'],
                'threadType' => $thread['type'],
                'type' => 'top',
            );

            $this->getNotificationService()->notify($thread['userId'], 'course-thread', $message);
        }

        return $this->createJsonResponse(true);
    }

    public function unstickAction(Request $request, $courseId, $threadId)
    {
        $thread = $this->getThreadService()->getThread($courseId, $threadId);
        $this->getThreadService()->unstickThread($courseId, $threadId);
        $user = $this->getCurrentUser();

        if ($user->isAdmin()) {
            $message = array(
                'courseId' => $courseId,
                'id' => $threadId,
                'title' => $thread['title'],
                'threadType' => $thread['type'],
                'type' => 'untop',
            );
            $this->getNotificationService()->notify($thread['userId'], 'course-thread', $message);
        }

        return $this->createJsonResponse(true);
    }

    public function eliteAction(Request $request, $courseId, $threadId)
    {
        $thread = $this->getThreadService()->getThread($courseId, $threadId);
        $this->getThreadService()->eliteThread($courseId, $threadId);
        $user = $this->getCurrentUser();

        if ($user->isAdmin()) {
            $message = array(
                'courseId' => $courseId,
                'id' => $threadId,
                'title' => $thread['title'],
                'threadType' => $thread['type'],
                'type' => 'elite',
            );
            $threadUrl = $this->generateUrl('course_thread_show', array('courseId' => $courseId, 'threadId' => $threadId), true);
            $this->getNotificationService()->notify($thread['userId'], 'course-thread', $message);
        }

        return $this->createJsonResponse(true);
    }

    public function uneliteAction(Request $request, $courseId, $threadId)
    {
        $thread = $this->getThreadService()->getThread($courseId, $threadId);
        $this->getThreadService()->uneliteThread($courseId, $threadId);
        $user = $this->getCurrentUser();

        if ($user->isAdmin()) {
            $message = array(
                'courseId' => $courseId,
                'id' => $threadId,
                'title' => $thread['title'],
                'threadType' => $thread['type'],
                'type' => 'unelite',
            );
            $threadUrl = $this->generateUrl('course_thread_show', array('courseId' => $courseId, 'threadId' => $threadId), true);
            $this->getNotificationService()->notify($thread['userId'], 'course-thread', $message);
        }

        return $this->createJsonResponse(true);
    }

    public function postAction(Request $request, $courseId, $threadId)
    {
        list($course, $member) = $this->getCourseService()->tryTakeCourse($courseId);

        if ($course['parentId']) {
            $classroom = $this->getClassroomService()->getClassroomByCourseId($course['id']);
            $classroomSetting = $this->getSettingService()->get('classroom');
            if (!$this->getClassroomService()->canLookClassroom($classroom['id'])) {
                return $this->createMessageResponse('info', '非常抱歉，您无权限访问该'.$classroomSetting['name'].'，如有需要请联系客服', '', 3, $this->generateUrl('homepage'));
            }
        }

        $thread = $this->getThreadService()->getThread($course['id'], $threadId);
        $form = $this->createPostForm(array(
            'courseId' => $thread['courseId'],
            'threadId' => $thread['id'],
        ));
        $currentUser = $this->getCurrentUser();

        if ('POST' == $request->getMethod()) {
            $form->submit($request);
            $userId = $currentUser->id;

            if ($form->isValid()) {
                $formData = $request->request->all();
                $postData = $form->getData();

                list($postData, $users) = $this->replaceMention($postData);

                $post = $this->getThreadService()->createPost($postData);

                $attachment = $request->request->get('attachment');
                $this->getUploadFileService()->createUseFiles($attachment['fileIds'], $post['id'], $attachment['targetType'], $attachment['type']);

                //notify不应该在这里做的，应该在Service里面做
                $this->getThreadService()->postAtNotifyEvent($post, $users);

                if ($thread['userId'] != $currentUser->id && 'question' != $thread['type']) {
                    $message = array(
                        'userId' => $currentUser['id'],
                        'userName' => $currentUser['nickname'],
                        'courseId' => $courseId,
                        'id' => $threadId,
                        'title' => $thread['title'],
                        'threadType' => $thread['type'],
                        'postId' => $post['id'],
                        'type' => 'reply',
                    );
                    $this->getNotificationService()->notify($thread['userId'], 'course-thread', $message);
                }

                foreach ($users as $user) {
                    if ($thread['userId'] != $user['id'] && 'question' != $thread['type']) {
                        if ($user['id'] != $userId) {
                            $message = array(
                                'userId' => $currentUser['id'],
                                'userName' => $currentUser['nickname'],
                                'courseId' => $courseId,
                                'id' => $threadId,
                                'title' => $thread['title'],
                                'threadType' => $thread['type'],
                                'postId' => $post['id'],
                                'type' => 'replayat',
                            );

                            $this->getNotificationService()->notify($user['id'], 'course-thread', $message);
                        }
                    }
                }

                return $this->render('course/thread/post-list-item.html.twig', array(
                    'course' => $course,
                    'thread' => $thread,
                    'post' => $post,
                    'author' => $this->getUserService()->getUser($post['userId']),
                    'isManager' => $this->getCourseService()->hasCourseManagerRole($course['id']),
                ));
            } else {
                return $this->createJsonResponse(false);
            }
        }

        return $this->render('course/thread/post.html.twig', array(
            'course' => $course,
            'member' => $member,
            'thread' => $thread,
            'form' => $form->createView(),
        ));
    }

    protected function replaceMention($postData)
    {
        $currentUser = $this->getCurrentUser();
        $content = $postData['content'];
        $users = array();
        preg_match_all('/@([\x{4e00}-\x{9fa5}\w]{2,16})/u', $content, $matches);
        $mentions = array_unique($matches[1]);

        foreach ($mentions as $mention) {
            $user = $this->getUserService()->getUserByNickname($mention);

            if ($user) {
                $path = $this->generateUrl('user_show', array('id' => $user['id']));
                $nickname = $user['nickname'];
                $html = "<a href=\"{$path}\" class=\"show-user\">@{$nickname}</a>";

                $content = preg_replace("/@{$nickname}/ui", $html, $content);

                $users[] = $user;
            }
        }

        $postData['content'] = $content;

        return array($postData, $users);
    }

    public function editPostAction(Request $request, $courseId, $threadId, $postId)
    {
        list($course, $member, $response) = $this->tryBuildCourseLayoutData($request, $courseId);

        if ($response) {
            return $response;
        }

        $post = $this->getThreadService()->getPost($courseId, $postId);

        if (empty($post)) {
            $this->createNewException(ThreadException::NOTFOUND_POST());
        }

        $user = $this->getCurrentUser();

        if ($user->isLogin() && $user->id == $post['userId']) {
            $course = $this->getCourseService()->getCourse($courseId);
        } else {
            $course = $this->getCourseService()->tryManageCourse($courseId);
        }

        $thread = $this->getThreadService()->getThread($courseId, $threadId);

        $form = $this->createPostForm($post);

        if ('POST' == $request->getMethod()) {
            $formData = $request->request->all();
            $form->submit($request);

            if ($form->isValid()) {
                $post = $this->getThreadService()->updatePost($post['courseId'], $post['id'], $form->getData());

                $attachment = $request->request->get('attachment');
                $this->getUploadFileService()->createUseFiles($attachment['fileIds'], $post['id'], $attachment['targetType'], $attachment['type']);
                if ($user->isAdmin()) {
                    $message = array(
                        'userId' => $user['id'],
                        'userName' => $user['nickname'],
                        'courseId' => $courseId,
                        'id' => $threadId,
                        'threadType' => $thread['type'],
                        'title' => $thread['title'],
                        'postId' => $post['id'],
                    );
                    $message['type'] = 'modify-thread';
                    $this->getNotificationService()->notify($thread['userId'], 'course-thread', $message);
                    $message['type'] = 'modify-post';
                    $this->getNotificationService()->notify($post['userId'], 'course-thread', $message);
                }

                $threadUrl = $this->generateUrl('course_thread_show', array('courseId' => $post['courseId'], 'threadId' => $post['threadId']), true);
                $threadUrl .= '?#post-'.$post['id']; // add ? to fix chrome bug

                return $this->redirect($threadUrl);
            }
        }

        return $this->render('course/thread/post-form.html.twig', array(
            'course' => $course,
            'member' => $member,
            'form' => $form->createView(),
            'post' => $post,
            'thread' => $thread,
        ));
    }

    public function deletePostAction(Request $request, $courseId, $threadId, $postId)
    {
        $post = $this->getThreadService()->getPost($courseId, $postId);
        $this->getThreadService()->deletePost($courseId, $postId);
        $user = $this->getCurrentUser();
        $thread = $this->getThreadService()->getThread($courseId, $threadId);

        if ($user->isAdmin()) {
            $threadUrl = $this->generateUrl('course_thread_show', array('courseId' => $courseId, 'threadId' => $threadId), true);

            $message = array(
                'userId' => $user['id'],
                'userName' => $user['nickname'],
                'courseId' => $courseId,
                'id' => $threadId,
                'postId' => $post['id'],
                'threadType' => $thread['type'],
                'title' => $thread['title'],
                'type' => 'delete-post',
            );

            $this->getNotificationService()->notify($thread['userId'], 'course-thread', $message);
            $this->getNotificationService()->notify($post['userId'], 'course-thread', $message);
        }

        return $this->createJsonResponse(true);
    }

    protected function getThreadSearchFilters($request)
    {
        $filters = array();
        $filters['type'] = $request->query->get('type');

        if (!in_array($filters['type'], array('all', 'question', 'discussion'))) {
            $filters['type'] = 'all';
        }

        $filters['sort'] = $request->query->get('sort');

        if (!in_array($filters['sort'], array('created', 'posted', 'createdNotStick', 'postedNotStick'))) {
            $filters['sort'] = 'posted';
        }
        $filters['isElite'] = $request->query->get('isElite');

        return $filters;
    }

    protected function convertFiltersToConditions($course, $filters)
    {
        $conditions = array('courseId' => $course['id'], 'isElite' => isset($filters['isElite']) ? $filters['isElite'] : '');

        switch ($filters['type']) {
            case 'question':
                $conditions['type'] = 'question';
                break;
            case 'discussion':
                $conditions['type'] = 'discussion';
                break;
            default:
                break;
        }

        return $conditions;
    }

    /**
     * @return ThreadService
     */
    protected function getThreadService()
    {
        return $this->getBiz()->service('Course:ThreadService');
    }

    /**
     * @return NotificationService
     */
    protected function getNotificationService()
    {
        return $this->getBiz()->service('User:NotificationService');
    }

    /**
     * @return VipService
     */
    protected function getVipService()
    {
        return $this->getBiz()->service('VipPlugin:Vip:VipService');
    }

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->getBiz()->service('Classroom:ClassroomService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->getBiz()->service('System:SettingService');
    }

    /**
     * @return UploadFileService
     */
    protected function getUploadFileService()
    {
        return $this->getBiz()->service('File:UploadFileService');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->getBiz()->service('Task:TaskService');
    }

    protected function getUserService()
    {
        return $this->getBiz()->service('User:UserService');
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
