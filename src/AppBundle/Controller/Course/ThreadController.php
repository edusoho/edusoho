<?php

namespace AppBundle\Controller\Course;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use Biz\Classroom\Service\ClassroomService;
use Biz\Course\MemberException;
use Biz\Course\Service\ThreadService;
use Biz\File\Service\UploadFileService;
use Biz\System\Service\SettingService;
use Biz\Task\Service\TaskService;
use Biz\Thread\ThreadException;
use Biz\User\Service\NotificationService;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use VipPlugin\Biz\Marketing\Service\VipRightService;
use VipPlugin\Biz\Marketing\VipRightSupplier\ClassroomVipRightSupplier;
use VipPlugin\Biz\Marketing\VipRightSupplier\CourseVipRightSupplier;
use VipPlugin\Biz\Vip\Service\VipService;

class ThreadController extends CourseBaseController
{
    public function indexAction(Request $request, $course, $member = [])
    {
        $courseMember = $this->getCourseMember($request, $course);
        if (empty($courseMember)) {
            $this->createNewException(MemberException::NOTFOUND_MEMBER());
        }
        $courseSet = $this->getCourseSetService()->getCourseSet($course['courseSetId']);

        $filters = $this->getThreadSearchFilters($request);
        $conditions = $this->convertFiltersToConditions($course, $filters);
        $conditions['excludeAuditStatus'] = 'illegal';

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

        return $this->render('course/tabs/threads.html.twig', [
            'type' => $conditions['type'],
            'courseSet' => $courseSet,
            'course' => $course,
            'member' => $member,
            'threads' => $threads,
            'users' => $users,
            'paginator' => $paginator,
            'filters' => $filters,
            'tasks' => $tasks,
            'target' => ['type' => 'course', 'id' => $course['id']],
        ]);
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
            $classroomSetting['name'] = empty($classroomSetting['name']) ? $this->trans('site.default.classroom') : $classroomSetting['name'];

            if (!$this->getClassroomService()->canLookClassroom($classroom['id'])) {
                return $this->createMessageResponse('info', '非常抱歉，您无权限访问该'.$classroomSetting['name'].'，如有需要请联系客服', '', 3, $this->generateUrl('homepage'));
            }
        }

        $user = $this->getCurrentUser();

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
            $elitePosts = [];
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

        return $this->render('course/thread/show.html.twig', [
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
        ]);
    }

    public function askVideoAction(Request $request, $threadId)
    {
        $thread = $this->getThreadService()->getThread(null, $threadId);
        $user = $this->getCurrentUser();

        return $this->render('course/thread/preview-modal.html.twig', [
            'courseId' => $thread['courseId'],
            'taskId' => $thread['taskId'],
            'videoAskTime' => $thread['videoAskTime'],
            'fileId' => $thread['videoId'],
            'userId' => $user['id'],
        ]);
    }

    public function playerShowAction(Request $request, $id)
    {
        return $this->forward('AppBundle:Player:show', [
            'id' => $id,
            'remeberLastPos' => false,
        ]);
    }

    public function createAction(Request $request, $courseId)
    {
        list($course, $member, $response) = $this->tryBuildCourseLayoutData($request, $courseId);

        if ($response) {
            return $response;
        }

        if ($member && !$this->getMemberService()->isMemberNonExpired($course, $member)) {
            return $this->redirect($this->generateUrl('my_course_show', ['id' => $courseId, 'tab' => 'threads']));
        }

        $type = $request->query->get('type') ?: 'discussion';
        $form = $this->createThreadForm([
            'type' => $type,
            'courseId' => $course['id'],
            'courseSetId' => $course['courseSetId'],
        ]);

        if ('POST' == $request->getMethod()) {

            // if(!$this->checkDragCaptchaToken($request, $request->request->get("_dragCaptchaToken", ""))){
            //     return $this->createMessageResponse('error', $this->trans("exception.form..drag.expire"));
            // }

            $form->handleRequest($request);
            if ($form->isValid()) {
                try {
                    $thread = $this->getThreadService()->createThread($form->getData());
                    $attachment = $request->request->get('attachment');
                    $this->getUploadFileService()->createUseFiles($attachment['fileIds'], $thread['id'], $attachment['targetType'], $attachment['type']);

                    return $this->redirect($this->generateUrl('course_thread_show', [
                        'courseId' => $thread['courseId'],
                        'threadId' => $thread['id'],
                    ]));
                } catch (\Exception $e) {
                    return $this->createMessageResponse('error', $this->trans($e->getMessage()), '错误提示', 1, $request->getPathInfo());
                }
            }
        }

        return $this->render('course/thread/form.html.twig', [
            'course' => $course,
            'member' => $member,
            'form' => $form->createView(),
            'type' => $type,
        ]);
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
        $course = $this->buildCourseTitle($course);

        $form = $this->createThreadForm($thread);

        if ('POST' == $request->getMethod()) {

            // if(!$this->checkDragCaptchaToken($request, $request->request->get("_dragCaptchaToken", ""))){
            //     return $this->createMessageResponse('error', $this->trans("exception.form..drag.expire"));
            // }

            try {
                $form->handleRequest($request);

                if ($form->isValid()) {
                    $thread = $this->getThreadService()->updateThread($thread['courseId'], $thread['id'], $form->getData());
                    $attachment = $request->request->get('attachment');
                    $this->getUploadFileService()->createUseFiles($attachment['fileIds'], $thread['id'], $attachment['targetType'], $attachment['type']);

                    if ($user->isAdmin()) {
                        $threadUrl = $this->generateUrl('course_thread_show', ['courseId' => $courseId, 'threadId' => $thread['id']], UrlGeneratorInterface::ABSOLUTE_URL);
                        $message = [
                            'courseId' => $courseId,
                            'id' => $thread['id'],
                            'title' => $thread['title'],
                            'threadType' => $thread['type'],
                            'type' => 'modify',
                        ];

                        $this->getNotificationService()->notify($thread['userId'], 'course-thread', $message);
                    }

                    return $this->redirect($this->generateUrl('course_thread_show', [
                        'courseId' => $thread['courseId'],
                        'threadId' => $thread['id'],
                    ]));
                }
            } catch (\Exception $e) {
                return $this->createMessageResponse('error', $this->trans($e->getMessage()), '错误提示', 1, $request->getPathInfo());
            }
        }

        return $this->render('course/thread/form.html.twig', [
            'form' => $form->createView(),
            'course' => $course,
            'member' => $member,
            'thread' => $thread,
            'type' => $thread['type'],
        ]);
    }

    protected function createThreadForm($data = [])
    {
        return $this->createNamedFormBuilder('thread', $data)
            ->add('title', TextType::class)
            ->add('content', TextareaType::class)
            ->add('type', HiddenType::class)
            ->add('courseId', HiddenType::class)
            ->getForm();
    }

    public function deleteAction(Request $request, $courseId, $threadId)
    {
        $thread = $this->getThreadService()->getThread($courseId, $threadId);
        $this->getThreadService()->deleteThread($thread['id']);
        $user = $this->getCurrentUser();

        if ($user->isAdmin()) {
            $threadUrl = $this->generateUrl('course_thread_show', ['courseId' => $courseId, 'threadId' => $threadId], UrlGeneratorInterface::ABSOLUTE_URL);
            $message = [
                'courseId' => $courseId,
                'id' => $threadId,
                'title' => $thread['title'],
                'threadType' => $thread['type'],
                'type' => 'delete',
            ];

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
            $message = [
                'courseId' => $courseId,
                'id' => $threadId,
                'title' => $thread['title'],
                'threadType' => $thread['type'],
                'type' => 'top',
            ];

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
            $message = [
                'courseId' => $courseId,
                'id' => $threadId,
                'title' => $thread['title'],
                'threadType' => $thread['type'],
                'type' => 'untop',
            ];
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
            $message = [
                'courseId' => $courseId,
                'id' => $threadId,
                'title' => $thread['title'],
                'threadType' => $thread['type'],
                'type' => 'elite',
            ];
            $threadUrl = $this->generateUrl('course_thread_show', ['courseId' => $courseId, 'threadId' => $threadId], UrlGeneratorInterface::ABSOLUTE_URL);
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
            $message = [
                'courseId' => $courseId,
                'id' => $threadId,
                'title' => $thread['title'],
                'threadType' => $thread['type'],
                'type' => 'unelite',
            ];
            $threadUrl = $this->generateUrl('course_thread_show', ['courseId' => $courseId, 'threadId' => $threadId], UrlGeneratorInterface::ABSOLUTE_URL);
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
            $classroomSetting['name'] = empty($classroomSetting['name']) ? $this->trans('site.default.classroom') : $classroomSetting['name'];

            if (!$this->getClassroomService()->canLookClassroom($classroom['id'])) {
                return $this->createMessageResponse('info', '非常抱歉，您无权限访问该'.$classroomSetting['name'].'，如有需要请联系客服', '', 3, $this->generateUrl('homepage'));
            }
        }

        $thread = $this->getThreadService()->getThread($course['id'], $threadId);
        $form = $this->createPostForm([
            'courseId' => $thread['courseId'],
            'threadId' => $thread['id'],
        ]);
        $currentUser = $this->getCurrentUser();

        if ('POST' == $request->getMethod()) {

            // if(!$this->checkDragCaptchaToken($request, $request->request->get("_dragCaptchaToken", ""))){
            //     return $this->createJsonResponse(['error' => ['code'=> 403, 'message' => $this->trans("exception.form..drag.expire")]], 403);
            // }

            $form->handleRequest($request);
            $userId = $currentUser->id;

            if ($form->isValid()) {
                $postData = $form->getData();

                list($postData, $users) = $this->replaceMention($postData);

                $post = $this->getThreadService()->createPost($postData);

                $attachment = $request->request->get('attachment');
                $this->getUploadFileService()->createUseFiles($attachment['fileIds'], $post['id'], $attachment['targetType'], $attachment['type']);

                //notify不应该在这里做的，应该在Service里面做
                $this->getThreadService()->postAtNotifyEvent($post, $users);

                if ($thread['userId'] != $currentUser->id && 'question' != $thread['type']) {
                    $message = [
                        'userId' => $currentUser['id'],
                        'userName' => $currentUser['nickname'],
                        'courseId' => $courseId,
                        'id' => $threadId,
                        'title' => $thread['title'],
                        'threadType' => $thread['type'],
                        'postId' => $post['id'],
                        'type' => 'reply',
                    ];
                    $this->getNotificationService()->notify($thread['userId'], 'course-thread', $message);
                }

                foreach ($users as $user) {
                    if ($thread['userId'] != $user['id'] && 'question' != $thread['type']) {
                        if ($user['id'] != $userId) {
                            $message = [
                                'userId' => $currentUser['id'],
                                'userName' => $currentUser['nickname'],
                                'courseId' => $courseId,
                                'id' => $threadId,
                                'title' => $thread['title'],
                                'threadType' => $thread['type'],
                                'postId' => $post['id'],
                                'type' => 'replayat',
                            ];

                            $this->getNotificationService()->notify($user['id'], 'course-thread', $message);
                        }
                    }
                }

                return $this->render('course/thread/post-list-item.html.twig', [
                    'course' => $course,
                    'thread' => $thread,
                    'post' => $post,
                    'author' => $this->getUserService()->getUser($post['userId']),
                    'isManager' => $this->getCourseService()->hasCourseManagerRole($course['id']),
                ]);
            } else {
                return $this->createJsonResponse(false);
            }
        }

        return $this->render('course/thread/post.html.twig', [
            'course' => $course,
            'member' => $member,
            'thread' => $thread,
            'form' => $form->createView(),
        ]);
    }

    protected function replaceMention($postData)
    {
        $content = $postData['content'];
        $users = [];
        preg_match_all('/@([\x{4e00}-\x{9fa5}\w]{2,16})/u', $content, $matches);
        $mentions = array_unique($matches[1]);

        foreach ($mentions as $mention) {
            $user = $this->getUserService()->getUserByNickname($mention);

            if ($user) {
                $path = $this->generateUrl('user_show', ['id' => $user['id']]);
                $nickname = $user['nickname'];
                $html = "<a href=\"{$path}\" class=\"show-user\">@{$nickname}</a>";

                $content = preg_replace("/@{$nickname}/ui", $html, $content);

                $users[] = $user;
            }
        }

        $postData['content'] = $content;

        return [$postData, $users];
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
        $course = $this->buildCourseTitle($course);

        $thread = $this->getThreadService()->getThread($courseId, $threadId);

        $form = $this->createPostForm($post);

        if ('POST' == $request->getMethod()) {
            // if(!$this->checkDragCaptchaToken($request, $request->request->get("_dragCaptchaToken", ""))){
            //     return $this->createMessageResponse('error', $this->trans("exception.form..drag.expire"));
            // }
            
            $form->handleRequest($request);

            if ($form->isValid()) {
                $post = $this->getThreadService()->updatePost($post['courseId'], $post['id'], $form->getData());

                $attachment = $request->request->get('attachment');
                $this->getUploadFileService()->createUseFiles($attachment['fileIds'], $post['id'], $attachment['targetType'], $attachment['type']);
                if ($user->isAdmin()) {
                    $message = [
                        'userId' => $user['id'],
                        'userName' => $user['nickname'],
                        'courseId' => $courseId,
                        'id' => $threadId,
                        'threadType' => $thread['type'],
                        'title' => $thread['title'],
                        'postId' => $post['id'],
                    ];
                    $message['type'] = 'modify-thread';
                    $this->getNotificationService()->notify($thread['userId'], 'course-thread', $message);
                    $message['type'] = 'modify-post';
                    $this->getNotificationService()->notify($post['userId'], 'course-thread', $message);
                }

                $threadUrl = $this->generateUrl('course_thread_show', ['courseId' => $post['courseId'], 'threadId' => $post['threadId']], UrlGeneratorInterface::ABSOLUTE_URL);
                $threadUrl .= '?#post-'.$post['id']; // add ? to fix chrome bug

                return $this->redirect($threadUrl);
            }
        }

        return $this->render('course/thread/post-form.html.twig', [
            'course' => $course,
            'member' => $member,
            'form' => $form->createView(),
            'post' => $post,
            'thread' => $thread,
        ]);
    }

    public function deletePostAction(Request $request, $courseId, $threadId, $postId)
    {
        $post = $this->getThreadService()->getPost($courseId, $postId);
        $this->getThreadService()->deletePost($courseId, $postId);
        $user = $this->getCurrentUser();
        $thread = $this->getThreadService()->getThread($courseId, $threadId);

        if ($user->isAdmin()) {
            $threadUrl = $this->generateUrl('course_thread_show', ['courseId' => $courseId, 'threadId' => $threadId], UrlGeneratorInterface::ABSOLUTE_URL);

            $message = [
                'userId' => $user['id'],
                'userName' => $user['nickname'],
                'courseId' => $courseId,
                'id' => $threadId,
                'postId' => $post['id'],
                'threadType' => $thread['type'],
                'title' => $thread['title'],
                'type' => 'delete-post',
            ];

            $this->getNotificationService()->notify($thread['userId'], 'course-thread', $message);
            $this->getNotificationService()->notify($post['userId'], 'course-thread', $message);
        }

        return $this->createJsonResponse(true);
    }

    protected function getThreadSearchFilters($request)
    {
        $filters = [];
        $filters['type'] = $request->query->get('type');

        if (!in_array($filters['type'], ['all', 'question', 'discussion'])) {
            $filters['type'] = 'all';
        }

        $filters['sort'] = $request->query->get('sort');

        if (!in_array($filters['sort'], ['created', 'posted', 'createdNotStick', 'postedNotStick'])) {
            $filters['sort'] = 'posted';
        }
        $filters['isElite'] = $request->query->get('isElite');

        return $filters;
    }

    protected function convertFiltersToConditions($course, $filters)
    {
        $conditions = ['courseId' => $course['id'], 'isElite' => isset($filters['isElite']) ? $filters['isElite'] : ''];

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

    /**
     * @return VipRightService
     */
    private function getVipRightService()
    {
        return $this->getBiz()->service('VipPlugin:Marketing:VipRightService');
    }

    protected function createPostForm($data = [])
    {
        return $this->createNamedFormBuilder('post', $data)
            ->add('content', TextareaType::class)
            ->add('courseId', HiddenType::class)
            ->add('threadId', HiddenType::class)
            ->getForm();
    }
}
