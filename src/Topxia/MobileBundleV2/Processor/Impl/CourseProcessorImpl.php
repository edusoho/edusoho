<?php

namespace Topxia\MobileBundleV2\Processor\Impl;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Exception\AbstractException;
use Biz\Favorite\Service\FavoriteService;
use Biz\Util\EdusohoLiveClient;
use Symfony\Component\HttpFoundation\Response;
use Topxia\MobileBundleV2\Processor\BaseProcessor;
use Topxia\MobileBundleV2\Processor\CourseProcessor;

class CourseProcessorImpl extends BaseProcessor implements CourseProcessor
{
    public function getVersion()
    {
        return $this->formData;
    }

    public function getCourseNotices()
    {
        $start = (int) $this->getParam('start', 0);
        $limit = (int) $this->getParam('limit', 10);
        $courseId = $this->getParam('courseId');

        if (empty($courseId)) {
            return [];
        }

        $conditions = [
            'targetType' => 'course',
            'targetId' => $courseId,
        ];

        $announcements = $this->getAnnouncementService()->searchAnnouncements(
            $conditions,
            ['createdTime' => 'DESC'],
            $start,
            $limit
        );
        $announcements = array_values($announcements);

        return $this->filterAnnouncements($announcements);
    }

    public function getLessonNote()
    {
        $courseId = $this->getParam('courseId');
        $lessonId = $this->getParam('lessonId');

        $user = $this->controller->getUserByToken($this->request);

        if (!$user->isLogin()) {
            return $this->createErrorResponse('not_login', '您尚未登录，不能查看笔记！');
        }

        $lessonNote = $this->controller->getNoteService()->getCourseNoteByUserIdAndTaskId($user['id'], $lessonId);

        if (empty($lessonNote)) {
            return null;
        }

        $task = $this->getTaskService()->getTask($lessonId);
        $lessonNote['lessonTitle'] = $task['title'];
        $lessonNote['lessonNum'] = $task['number'];
        $lessonNote['lessonId'] = $lessonId;

        $content = $this->controller->convertAbsoluteUrl($this->request, $lessonNote['content']);
        $content = $this->filterNote($content);
        $lessonNote['content'] = $content;

        return $lessonNote;
    }

    public function getCourseMember()
    {
        $courseId = $this->getParam('courseId');
        $user = $this->controller->getUserByToken($this->request);

        if (empty($courseId)) {
            return null;
        }

        $member = $user->isLogin() ? $this->controller->getCourseMemberService()->getCourseMember(
            $courseId,
            $user['id']
        ) : null;
        $member = $this->previewAsMember($member, $courseId, $user);

        if ($member && $member['locked']) {
            return null;
        }

        $member = $this->checkMemberStatus($member);

        return empty($member) ? new Response('null') : $member;
    }

    public function postThread()
    {
        $courseId = $this->getParam('courseId', 0);
        $threadId = $this->getParam('threadId', 0);

        $user = $this->controller->getUserByToken($this->request);

        if (!$user->isLogin()) {
            return $this->createErrorResponse('not_login', '您尚未登录，不能回复！');
        }

        $thread = $this->controller->getThreadService()->getThread($courseId, $threadId);

        if (empty($thread)) {
            return $this->createErrorResponse('not_thread', '问答不存在或已删除');
        }

        $content = $this->getParam('content', '');
        $content = $this->uploadImage($content);

        $formData = $this->formData;
        $formData['content'] = $content;
        unset($formData['imageCount']);
        $post = $this->controller->getThreadService()->createPost($formData);
        $post['createdTime'] = date('c', $post['createdTime']);

        return $post;
    }

    /*
     *更新回复
     */
    public function updatePost()
    {
        $courseId = $this->getParam('courseId', 0);
        $threadId = $this->getParam('threadId', 0);
        $postId = $this->getParam('postId', 0);

        $user = $this->controller->getUserByToken($this->request);

        if (!$user->isLogin()) {
            return $this->createErrorResponse('not_login', '您尚未登录，不能评价课程！');
        }

        if (!empty($postId)) {
            $post = $this->controller->getThreadService()->getPost($courseId, $postId);

            if (empty($post)) {
                return $this->createErrorResponse('postId_not_exist', 'postId不存在！');
            }
        } else {
            return $this->createErrorResponse('wrong_postId_param', 'postId参数错误！');
        }

        $content = $this->getParam('content', '');

        if (empty($content)) {
            return $this->createErrorResponse('wrong_content_param', '回复内容不能为空！');
        }

        $content = $this->uploadImage($content);

        $formData = $this->formData;
        $formData['content'] = $content;
        unset($formData['imageCount']);

        $post = $this->controller->getThreadService()->updatePost($courseId, $postId, $formData);

        $threadInfo = $this->controller->getThreadService()->getThread($courseId, $threadId);

        return $post;
    }

    /**
     * add need param (courseId, lessonId, title, content, type="question")
     * update need param (courseId, threadId, title, content, type).
     */
    public function updateThread()
    {
        $courseId = $this->getParam('courseId', 0);
        $threadId = $this->getParam('threadId', 0);
        $title = $this->getParam('title', '');
        $content = $this->getParam('content', '');
        $action = $this->getParam('action', 'update');
        $imageCount = $this->getParam('imageCount', 0);

        $user = $this->controller->getUserByToken($this->request);

        if (!$user->isLogin()) {
            return $this->createErrorResponse('not_login', '您尚未登录，修改该课时');
        }

        if ($imageCount > 0) {
            $content = $this->uploadImage($content);
        }

        $formData = $this->formData;
        $formData['content'] = $content;
        unset($formData['imageCount']);
        unset($formData['action']);
        unset($formData['threadId']);

        $result = [];

        if ('add' == $action) {
            $result = $this->controller->getThreadService()->createThread($formData);
        } else {
            $fields = [
                'title' => $title,
                'content' => $content,
            ];
            $result = $this->controller->getThreadService()->updateThread($courseId, $threadId, $fields);
        }

        $result['content'] = $this->filterSpace(
            $this->controller->convertAbsoluteUrl($this->controller->request, $result['content'])
        );
        $result['latestPostTime'] = date('c', $result['latestPostTime']);
        $result['createdTime'] = date('c', $result['createdTime']);

        return $result;
    }

    private function uploadImage($content)
    {
        $url = 'none';
        $urlArray = [];
        $files = $file = $this->request->files;

        foreach ($files as $key => $value) {
            try {
                $group = $this->getParam('group', 'course');
                $record = $this->getFileService()->uploadFile($group, $value);
                $url = $this->controller->get('web.twig.extension')->getFilePath($record['uri']);
            } catch (\Exception $e) {
                $url = 'error';
            }

            $urlArray[$key] = $url;
        }

        $baseUrl = $this->request->getSchemeAndHttpHost();
        $content = preg_replace_callback(
            '/src=[\'\"](.*?)[\'\"]/',
            function ($matches) use ($baseUrl, $urlArray) {
                if (false !== strpos($matches[1], 'http')) {
                    return "src=\"$matches[1]\"";
                } else {
                    return "src=\"{$urlArray[$matches[1]]}\"";
                }
            },
            $content
        );

        return $content;
    }

    public function commitCourse()
    {
        $courseId = $this->getParam('courseId', 0);
        $user = $this->controller->getUserByToken($this->request);

        if (!$user->isLogin()) {
            return $this->createErrorResponse($this->request, 'not_login', '您尚未登录，不能评价课程！');
        }

        $course = $this->controller->getCourseService()->getCourse($courseId);

        if (empty($course)) {
            return $this->createErrorResponse('not_found', "课程#{$courseId}不存在，不能评价！");
        }

        if (!$this->controller->getCourseService()->canTakeCourse($course)) {
            return $this->createErrorResponse('access_denied', "您不是课程《{$course['title']}》学员，不能评价课程！");
        }

        $review = [];
        $review['targetType'] = 'course';
        $review['targetId'] = $course['id'];
        $review['userId'] = $user['id'];
        $review['rating'] = (float) $this->getParam('rating', 0);
        $review['content'] = $this->getParam('content', '');

        $existed = $this->controller->getReviewService()->getByUserIdAndTargetTypeAndTargetId($user['id'], 'course', $courseId);

        if (empty($existed)) {
            $review = $this->controller->getReviewService()->createReview($review);
        } else {
            $review = $this->controller->getReviewService()->updateReview($existed['id'], $review);
        }

        $review = $this->controller->filterReview($review);

        return $review;
    }

    public function getCourseThreads()
    {
        $user = $this->controller->getUserByToken($this->request);

        if (!$user->isLogin()) {
            return $this->createErrorResponse('not_login', '您尚未登录，不能查看该课时');
        }

        $type = $this->getParam('type', 'question');
        $lessonId = $this->getParam('lessonId', '0');

        if ('0' == $lessonId) {
            $conditions = [
                'userId' => $user['id'],
                'type' => $type,
            ];
        } else {
            $conditions = [
                'lessonId' => $lessonId,
                'type' => $type,
            ];
        }

        $start = (int) $this->getParam('start', 0);
        $limit = (int) $this->getParam('limit', 10);
        $total = $this->controller->getThreadService()->searchThreadCount($conditions);

        $threads = $this->controller->getThreadService()->searchThreads($conditions, 'postedNotStick', $start, $limit);
        $controller = $this;
        $threads = array_map(
            function ($thread) use ($controller) {
                $thread['content'] = $controller->filterSpace(
                    $controller->controller->convertAbsoluteUrl($controller->request, $thread['content'])
                );

                return $thread;
            },
            $threads
        );

        $courses = $this->controller->getCourseService()->findCoursesByIds(ArrayToolkit::column($threads, 'courseId'));

        $posts = [];

        foreach ($threads as $key => $thread) {
            $post = $this->controller->getThreadService()->findThreadPosts(
                $thread['courseId'],
                $thread['id'],
                'elite',
                0,
                1
            );

            if (!empty($post)) {
                $posts[$post[0]['threadId']] = $post[0];
            }
        }

        $threads = array_map(
            function ($thread) use ($posts) {
                if (isset($posts[$thread['id']])) {
                    $thread['latestPostContent'] = $posts[$thread['id']]['content'];
                }

                return $thread;
            },
            $threads
        );

        $users = $this->controller->getUserService()->findUsersByIds(ArrayToolkit::column($threads, 'userId'));
        $threads = $this->filterThreads($threads, $courses, $this->filterUsersFiled($users));

        return [
            'start' => $start,
            'limit' => $limit,
            'total' => count($threads),
            'data' => $threads,
        ];
    }

    public function getCourseNotes()
    {
        $start = $this->getParam('start', 0);
        $limit = $this->getParam('limit', 10);
        $courseId = $this->getParam('courseId');

        $user = $this->controller->getUserByToken($this->request);

        if (!$user->isLogin()) {
            return $this->createErrorResponse('not_login', '您尚未登录，不能查看笔记！');
        }

        $conditions = [
            'userId' => $user['id'],
            'courseId' => $courseId,
            'noteNumGreaterThan' => 0,
        ];

        $courseNotes = $this->controller->getNoteService()->searchNotes(
            $conditions,
            ['createdTime' => 'DESC'],
            $start,
            $limit
        );
        $lessons = $this->controller->getCourseService()->findLessonsByIds(
            ArrayToolkit::column($courseNotes, 'lessonId')
        );

        for ($i = 0; $i < count($courseNotes); ++$i) {
            $courseNote = $courseNotes[$i];
            $lesson = $lessons[$courseNote['lessonId']];
            $courseNote['lessonTitle'] = $lesson['title'];
            $courseNote['lessonNum'] = $lesson['number'];
            $content = $this->controller->convertAbsoluteUrl($this->request, $courseNote['content']);
            $content = $this->filterNote($content);
            $courseNote['content'] = $content;
            $courseNotes[$i] = $courseNote;
        }

        return $courseNotes;
    }

    private function filterNote($note)
    {
        return preg_replace_callback(
            '/<img [^>]+\\/?>/',
            function ($matches) {
                return '<p>'.$matches[0].'</p>';
            },
            $note
        );
    }

    public function getNoteList()
    {
        $user = $this->controller->getUserByToken($this->request);
        $start = $this->getParam('start', 0);
        $limit = $this->getParam('limit', 10);

        if (!$user->isLogin()) {
            return $this->createErrorResponse('not_login', '您尚未登录，不能查看笔记！');
        }

        $conditions = [
            'userId' => $user['id'],
        ];

        $total = $this->controller->getNoteService()->countCourseNotes($conditions);
        $noteInfos = $this->controller->getNoteService()->searchNotes(
            $conditions,
            ['updatedTime' => 'DESC'],
            $start,
            $limit
        );
        $lessonIds = ArrayToolkit::column($noteInfos, 'lessonId');
        $lessons = $this->getCourseService()->findLessonsByIds($lessonIds);

        for ($i = 0; $i < count($noteInfos); ++$i) {
            $note = $noteInfos[$i];
            $noteInfos[$i]['updatedTime'] = date('c', $note['createdTime']);
            $noteInfos[$i]['createdTime'] = date('c', $note['createdTime']);
            $noteInfos[$i]['lessonTitle'] = $lessons[$note['lessonId']]['title'];
        }

        return $noteInfos;
    }

    public function getOneNote()
    {
        $user = $this->controller->getUserByToken($this->request);

        if (!$user->isLogin()) {
            return $this->createErrorResponse('not_login', '您尚未登录，不能查看笔记！');
        }

        $noteId = $this->getParam('noteId', 0);
        $noteInfo = $this->controller->getNoteService()->getNote($noteId);
        $lessonInfo = $this->controller->getCourseService()->getCourseLesson(
            $noteInfo['courseId'],
            $noteInfo['lessonId']
        );
        $lessonStatus = $this->controller->getCourseService()->getUserLearnLessonStatus(
            $user['id'],
            $noteInfo['courseId'],
            $noteInfo['lessonId']
        );
        $noteContent = $this->filterSpace($this->controller->convertAbsoluteUrl($this->request, $noteInfo['content']));
        $noteInfos = [
            'courseId' => $noteInfo['courseId'],
            'courseTitle' => null,
            'noteLastUpdateTime' => null,
            'lessonId' => $lessonInfo['id'],
            'lessonTitle' => $lessonInfo['title'],
            'learnStatus' => $lessonStatus,
            'content' => $noteContent,
            'createdTime' => date('c', $noteInfo['createdTime']),
            'noteNum' => null,
            'largePicture' => null,
        ];

        return $noteInfos;
    }

    public function AddNote()
    {
        $courseId = $this->getParam('courseId', 0);
        $lessonId = $this->getParam('lessonId', 0);
        $content = $this->getParam('content', '');

        $user = $this->controller->getUserByToken($this->request);

        if (!$user->isLogin()) {
            return $this->createErrorResponse('not_login', '您尚未登录，不能查看笔记！');
        }

        $noteInfo = [
            'content' => $content,
            'lessonId' => $lessonId,
            'courseId' => $courseId,
        ];

        $content = $this->getParam('content', '');

        if (empty($content)) {
            return $this->createErrorResponse('wrong_content_param', '笔记内容不能为空！');
        }

        $noteInfo['content'] = $this->uploadImage($content);

        $result = $this->controller->getNoteService()->createCourseNote($noteInfo);
        $result['content'] = $this->controller->convertAbsoluteUrl($this->request, $result['content']);

        if (0 == $result['updatedTime']) {
            $result['updatedTime'] = $result['createdTime'];
        }

        $result['createdTime'] = date('c', $result['createdTime']);
        $result['updatedTime'] = date('c', $result['updatedTime']);

        return $result;
    }

    public function DeleteNote()
    {
        $id = $this->getParam('id', 0);
        $user = $this->controller->getUserByToken($this->request);

        if (!$user->isLogin()) {
            return $this->createErrorResponse('not_login', '您尚未登录，不能查看笔记！');
        }

        return $this->controller->getNoteService()->deleteNote($id);
    }

    private function filterThreads($threads, $courses, $users)
    {
        if (empty($threads)) {
            return [];
        }

        for ($i = 0; $i < count($threads); ++$i) {
            $thread = $threads[$i];

            if (!isset($courses[$thread['courseId']])) {
                unset($threads[$i]);
                continue;
            }

            $course = $courses[$thread['courseId']];

            if (0 != $thread['lessonId']) {
                $lessonInfo = $this->controller->getCourseService()->findLessonsByIds(
                    [
                        $thread['lessonId'],
                    ]
                );
                $thread['number'] = $lessonInfo[$thread['lessonId']]['number'];
            } else {
                $thread['number'] = 0;
            }

            $threads[$i] = $this->filterThread($thread, $course, $users[$thread['userId']]);
        }

        return $threads;
    }

    private function filterThread($thread, $course, $user)
    {
        $thread['courseTitle'] = $course['title'];

        $thread['coursePicture'] = $this->controller->coverPath($course['largePicture'], 'course.png');

        $isTeacherPost = $this->controller->getThreadService()->findThreadElitePosts(
            $course['id'],
            $thread['id'],
            0,
            100
        );
        $thread['isTeacherPost'] = empty($isTeacherPost) ? false : true;
        $user['smallAvatar'] = $this->controller->getContainer()->get('web.twig.extension')->getFurl(
            $user['smallAvatar'],
            'avatar.png'
        );
        $user['mediumAvatar'] = $this->controller->getContainer()->get('web.twig.extension')->getFurl(
            $user['mediumAvatar'],
            'avatar.png'
        );
        $user['largeAvatar'] = $this->controller->getContainer()->get('web.twig.extension')->getFurl(
            $user['largeAvatar'],
            'avatar.png'
        );
        $thread['user'] = $user;
        $thread['createdTime'] = date('c', $thread['createdTime']);
        $thread['latestPostTime'] = date('c', $thread['latestPostTime']);
        if (isset($thread['updatedTime'])) {
            $thread['updatedTime'] = date('c', $thread['updatedTime']);
        }

        return $thread;
    }

    public function getThreadPost()
    {
        $courseId = $this->getParam('courseId', 0);
        $threadId = $this->getParam('threadId', 0);
        $start = (int) $this->getParam('start', 0);
        $limit = (int) $this->getParam('limit', 10);

        $user = $this->controller->getUserByToken($this->request);

        if (!$user->isLogin()) {
            return $this->createErrorResponse('not_login', '您尚未登录，不能查看该课时');
        }

        $total = $this->controller->getThreadService()->getThreadPostCount($courseId, $threadId);
        $posts = $this->controller->getThreadService()->findThreadPosts($courseId, $threadId, 'elite', $start, $limit);
        $users = $this->controller->getUserService()->findUsersByIds(ArrayToolkit::column($posts, 'userId'));

        $controller = $this;
        $posts = array_map(
            function ($post) use ($controller) {
                $post['content'] = $controller->filterSpace(
                    $controller->controller->convertAbsoluteUrl($controller->request, $post['content'])
                );

                return $post;
            },
            $posts
        );

        return [
            'start' => $start,
            'limit' => $limit,
            'total' => $total,
            'data' => $this->filterPosts($posts, $this->controller->filterUsers($users)),
        ];
    }

    public function getOneThreadPost()
    {
        $courseId = $this->getParam('courseId', 0);
        $postId = $this->getParam('postId', 0);
        $user = $this->controller->getUserByToken($this->request);

        if (!$user->isLogin()) {
            return $this->createErrorResponse('not_login', '您尚未登录，不能查看该课时');
        }

        $post = $this->controller->getThreadService()->getPost($courseId, $postId);

        if (null == $post) {
            return $this->createErrorResponse('no_post', '没有找到指定回复!');
        } else {
            $post['createdTime'] = date('c', $post['createdTime']);
        }

        return $post;
    }

    public function getThread()
    {
        $courseId = $this->getParam('courseId', 0);
        $threadId = $this->getParam('threadId', 0);
        $user = $this->controller->getUserByToken($this->request);

        if (!$user->isLogin()) {
            return $this->createErrorResponse('not_login', '您尚未登录，不能查看该课时');
        }

        $thread = $this->controller->getThreadService()->getThread($courseId, $threadId);

        if (empty($thread)) {
            return $this->createErrorResponse('no_thread', '没有找到指定问答!');
        }

        $course = $this->controller->getCourseService()->getCourse($thread['courseId']);
        $user = $this->controller->getUserService()->getUser($thread['userId']);

        $user['following'] = (string) $this->controller->getUserService()->findUserFollowingCount($user['id']);
        $user['follower'] = (string) $this->controller->getUserService()->findUserFollowerCount($user['id']);
        $result = $this->filterThread($thread, $course, $user);
        $result['content'] = $this->filterSpace(
            $this->controller->convertAbsoluteUrl($this->request, $result['content'])
        );

        return $result;
    }

    public function getThreadTeacherPost()
    {
        $courseId = $this->getParam('courseId', 0);
        $threadId = $this->getParam('threadId', 0);

        $user = $this->controller->getUserByToken($this->request);

        if (!$user->isLogin()) {
            return $this->createErrorResponse('not_login', '您尚未登录，不能查看该课时');
        }

        $posts = $this->controller->getThreadService()->findThreadElitePosts($courseId, $threadId, 0, 100);
        $users = $this->controller->getUserService()->findUsersByIds(ArrayToolkit::column($posts, 'userId'));

        return $this->filterPosts($posts, $this->controller->filterUsers($users));
    }

    private function filterPosts($posts, $users)
    {
        return array_map(
            function ($post) use ($users) {
                $post['user'] = $users[$post['userId']];
                $post['createdTime'] = date('c', $post['createdTime']);

                return $post;
            },
            $posts
        );
    }

    public function getFavoriteLiveCourse()
    {
        $result = $this->getFavoriteCourseByCourseType('live');
        if (isset($result['error'])) {
            return $result;
        }

        return $result;
    }

    public function getFavoriteNormalCourse()
    {
        $result = $this->getFavoriteCourseByCourseType('normal');
        if (isset($result['error'])) {
            return $result;
        }

        return $result;
    }

    protected function getFavoriteCourseByCourseType($courseType)
    {
        $user = $this->controller->getUserByToken($this->request);

        if (!$user->isLogin()) {
            return $this->createErrorResponse('not_login', '您尚未登录，不能查看该课时');
        }

        $start = (int) $this->getParam('start', 0);
        $limit = (int) $this->getParam('limit', 10);

        $total = (int) $this->controller->getCourseService()->countUserFavoriteCourseNotInClassroomWithCourseType(
            $user['id'],
            $courseType
        );
        $courses = $this->controller->getCourseService()->findUserFavoriteCoursesNotInClassroomWithCourseType(
            $user['id'],
            $courseType,
            $start,
            $limit
        );
        $courses = $this->controller->filterCourses($courses);

        return [
            'start' => $start,
            'limit' => $limit,
            'total' => $total,
            'data' => array_values($courses),
        ];
    }

    public function getFavoriteCourse()
    {
        $user = $this->controller->getUserByToken($this->request);

        if (!$user->isLogin()) {
            return $this->createErrorResponse('not_login', '您尚未登录，不能查看该课时');
        }

        $start = (int) $this->getParam('start', 0);
        $limit = (int) $this->getParam('limit', 10);

        $total = $this->controller->getCourseService()->findUserFavoritedCourseCountNotInClassroom($user['id']);
        $courses = $this->controller->getCourseService()->findUserFavoritedCoursesNotInClassroom($user['id'], $start, $limit);

        return [
            'start' => $start,
            'limit' => $limit,
            'total' => $total,
            'data' => $this->controller->filterCourses($courses),
        ];
    }

    public function getCourseReviewInfo()
    {
        $courseId = $this->getParam('courseId', 0);
        $course = $this->controller->getCourseService()->getCourse($courseId);
        $total = $this->controller->getReviewService()->countReviews(['targetType' => 'course', 'targetId' => $courseId]);
        $reviews = $this->controller->getReviewService()->searchReviews(
            ['targetType' => 'course', 'targetId' => $courseId],
            ['id' => 'ASC'],
            0, $total);

        $progress = [0, 0, 0, 0, 0];

        foreach ($reviews as $key => $review) {
            if ($review['rating'] < 1) {
                continue;
            }
            ++$progress[$review['rating'] - 1];
        }

        return [
            'info' => [
                'ratingNum' => empty($course['ratingNum']) ? 0 : $course['ratingNum'],
                'rating' => empty($course['rating']) ? 0 : $course['rating'],
            ],
            'progress' => $progress,
        ];
    }

    public function getReviews()
    {
        $courseId = $this->getParam('courseId');

        $start = (int) $this->getParam('start', 0);
        $limit = (int) $this->getParam('limit', 10);
        $total = $this->controller->getReviewService()->countReviews(['targetId' => $courseId, 'targetType' => 'course', 'parentId' => 0]);
        $reviews = $this->controller->getReviewService()->searchReviews(
            ['targetId' => $courseId, 'targetType' => 'course', 'parentId' => 0], ['id' => 'ASC'],
            $start, $limit);
        $reviews = $this->controller->filterReviews($reviews);

        return [
            'start' => $start,
            'limit' => $limit,
            'total' => $total,
            'data' => $reviews,
        ];
    }

    public function favoriteCourse()
    {
        $user = $this->controller->getUserByToken($this->request);
        $courseId = $this->getParam('courseId');

        if (empty($user) || !$user->isLogin()) {
            return $this->createErrorResponse('not_login', '您尚未登录，不能收藏课程！');
        }

        $course = $this->getCourseService()->getCourse($courseId);

        $this->getFavoriteService()->createFavorite(['targetType' => 'course', 'targetId' => $course['courseSetId'], 'userId' => $user['id']]);

        return true;
    }

    public function getTeacherCourses()
    {
        $userId = $this->getParam('userId');

        if (empty($userId)) {
            return [];
        }

        $courses = $this->controller->getCourseService()->findUserTeachCourses(['userId' => $userId, 'excludeTypes' => ['reservation']], 0, 10);
        $courses = $this->controller->filterCourses($courses);

        return $courses;
    }

    public function unFavoriteCourse()
    {
        $user = $this->controller->getUserByToken($this->request);
        $courseId = $this->getParam('courseId');

        if (empty($user) || !$user->isLogin()) {
            return $this->createErrorResponse('not_login', '您尚未登录，不能收藏课程！');
        }

        $course = $this->getCourseService()->getCourse($courseId);

        if (empty($this->getFavoriteService()->getUserFavorite($user['id'], 'course', $course['courseSetId']))) {
            return $this->createErrorResponse('runtime_error', '您尚未收藏课程，不能取消收藏！');
        }

        try {
            $this->getFavoriteService()->deleteUserFavorite($user['id'], 'course', $course['courseSetId']);
        } catch (AbstractException $e) {
            return $this->createErrorResponse('runtime_error', $e->getMessage());
        }

        return true;
    }

    public function vipLearn()
    {
        if (!$this->controller->isinstalledPlugin('Vip') || !$this->controller->setting('vip.enabled')) {
            return $this->createErrorResponse('error', '网校没有开启vip功能');
        }

        $courseId = $this->getParam('courseId');
        $user = $this->controller->getUserByToken($this->request);

        if (!$user->isLogin()) {
            return $this->createErrorResponse('not_login', '您尚未登录，不能收藏课程！');
        }

        $vip = $this->controller->getVipService()->getMemberByUserId($user['id']);

        if (empty($vip)) {
            return $this->createErrorResponse('error', '用户不是vip会员!');
        }

        try {
            list($success, $message) = $this->getVipFacadeService()->joinCourse($courseId);
            if (!$success) {
                return $this->createErrorResponse('error', $message);
            }
        } catch (AbstractException $e) {
            return $this->createErrorResponse('error', $e->getMessage());
        }

        return true;
    }

    public function coupon()
    {
        $code = $this->getParam('code');
        $type = $this->getParam('type');
        $courseId = $this->getParam('courseId');
        //判断coupon是否合法，是否存在跟是否过期跟是否可用于当前课程
        $course = $this->controller->getCourseService()->getCourse($courseId);
        $couponInfo = $this->getCouponService()->checkCouponUseable($code, $type, $courseId, $course['price']);

        $result['data'] = null;

        if (empty($couponInfo)) {
            return $this->createErrorResponse('error', '优惠码不存在!');

            return $result;
        }

        if ('no' == $couponInfo['useable']) {
            return $this->createErrorResponse('error', '优惠码已使用!');

            return $result;
        }

        return $couponInfo;
    }

    public function unLearnCourse()
    {
        $courseId = $this->getParam('courseId');
        $user = $this->controller->getUserByToken($this->request);

        if (!$user->isLogin()) {
            return $this->createErrorResponse('no_login', '您尚未登录，不能查看该课时');
        }

        list($course, $member) = $this->controller->getCourseService()->tryTakeCourse($courseId);

        if (empty($member)) {
            return $this->createErrorResponse('error', '您不是课程的学员或尚未购买该课程，不能退学。');
        }

        $reason = $this->getParam('reason', '');

        try {
            $this->getCourseMemberService()->removeStudent($course['id'], $user['id'], [
                'reason' => $reason,
            ]);
        } catch (\Exception $e) {
            return $this->createErrorResponse('error', $e->getMessage());
        }

        return true;
    }

    public function getCourse()
    {
        $user = $this->controller->getUserByToken($this->request);
        $courseId = $this->getParam('courseId');
        $course = $this->controller->getCourseService()->getCourse($courseId);
        $courseSet = $this->getCourseSetService()->getCourseSet($course['courseSetId']);
        if (empty($course)) {
            return $this->createErrorResponse('not_found', '课程不存在');
        }

        $member = $user->isLogin() ? $this->controller->getCourseMemberService()->getCourseMember(
            $course['id'],
            $user['id']
        ) : null;
        $member = $this->previewAsMember($member, $courseId, $user);

        if ($member && $member['locked']) {
            return $this->createErrorResponse('member_locked', '会员被锁住，不能访问课程，请联系管理员!');
        }

        if ('published' != $course['status']) {
            if (!$user->isLogin()) {
                return $this->createErrorResponse('course_not_published', '课程未发布或已关闭。');
            }

            if (empty($member)) {
                return $this->createErrorResponse('course_not_published', '课程未发布或已关闭。');
            }

            $deadline = $member['deadline'];
            $createdTime = $member['createdTime'];

            if (0 != $deadline && ($deadline - $createdTime) < 0) {
                return $this->createErrorResponse('course_not_published', '课程未发布或已关闭。');
            }
        }

        if (empty($member)) {
            $member = $this->controller->getCourseMemberService()->becomeStudentByClassroomJoined(
                $courseId,
                $user['id']
            );
            if (empty($member)) {
                $member = null;
            }
        }

        //老接口VIP加入，没有orderId
        if ($this->isUserVipExpire($course, $member)) {
            return $this->createErrorResponse('user.vip_expired', '会员已过期，请重新加入课程！');
        }

        $this->updateMemberLastViewTime($member);
        $userFavorited = $user->isLogin() ? !empty($this->getFavoriteService()->getUserFavorite($user['id'], 'course', $course['courseSetId'])) : false;
        $vipLevels = [];

        if ($this->controller->isinstalledPlugin('Vip') && $this->controller->setting('vip.enabled')) {
            $vipLevels = $this->controller->getLevelService()->searchLevels(
                [
                    'enabled' => 1,
                ],
                [],
                0,
                100
            );
        }

        $course['source'] = $this->setCourseTarget($course['id']);

        return [
            'course' => $this->controller->filterCourse($course),
            'userFavorited' => $userFavorited ? true : false,
            'member' => $this->checkMemberStatus($member),
            'vipLevels' => $vipLevels,
            'discount' => $this->getCourseDiscount($courseSet['discountId']),
        ];
    }

    private function setCourseTarget($courseId)
    {
        $classroom = $this->controller->getClassroomService()->getClassroomByCourseId($courseId);

        return empty($classroom) ? null : 'classroom';
    }

    private function getCourseDiscount($discountId)
    {
        if ($this->controller->isinstalledPlugin('Discount')) {
            $discount = $this->getDiscountService()->getDiscount($discountId);

            if (empty($discount)) {
                return null;
            }

            $discount['startTime'] = date('c', $discount['startTime']);
            $discount['endTime'] = date('c', $discount['endTime']);
            $discount['changeTime'] = date('c', $discount['changeTime']);
            $discount['auditedTime'] = date('c', $discount['auditedTime']);
            $discount['createdTime'] = date('c', $discount['createdTime']);

            return $discount;
        }

        return null;
    }

    public function searchCourse()
    {
        $search = $this->getParam('search', '');
        $tagId = $this->getParam('tagId', '');
        $categoryId = (int) $this->getParam('categoryId', 0);
        $type = $this->getParam('type', 'normal');

        if (0 != $categoryId) {
            $conditions['categoryId'] = $categoryId;
        }

        //过滤掉约排课
        $courseSets = $this->getCourseSetService()->searchCourseSets(['title' => $search, 'excludeTypes' => ['reservation']], [], 0, PHP_INT_MAX);

        $conditions['courseSetIds'] = ArrayToolkit::column($courseSets, 'id');

        if (!empty($tagId)) {
            $conditions['tagId'] = $tagId;
        }

        if (empty($conditions['courseSetIds'])) {
            return [
                'start' => (int) $this->getParam('start', 0),
                'limit' => (int) $this->getParam('limit', 10),
                'total' => 0,
                'data' => [],
            ];
        }

        return $this->findCourseByConditions($conditions, $type);
    }

    public function getCourses()
    {
        $categoryId = (int) $this->getParam('categoryId', 0);
        $conditions = [];

        if (0 != $categoryId) {
            $conditions['categoryId'] = $categoryId;
        }

        return $this->findCourseByConditions($conditions, 'normal');
    }

    private function findCourseByConditions($conditions, $type)
    {
        $conditions['status'] = 'published';
        $conditions['parentId'] = '0';
        //过滤约排课
        $conditions['excludeTypes'] = ['reservation'];

        if (empty($type)) {
            unset($conditions['type']);
        } else {
            $conditions['type'] = $type;
        }

        $start = (int) $this->getParam('start', 0);
        $limit = (int) $this->getParam('limit', 10);
        $total = $this->controller->getCourseService()->countCourses($conditions);
        $sort = $this->getParam('sort', ['createdTime' => 'desc']);

        if ('recommendedSeq' == $sort) {
            $conditions['recommended'] = 1;
            $recommendCount = $this->getCourseService()->countCourses($conditions);

            //先按推荐顺序展示推荐，再追加非推荐
            $courses = $this->controller->getCourseService()->searchCourses($conditions, $sort, $start, $limit);

            if (($start + $limit) > $recommendCount) {
                $conditions['recommended'] = 0;
                if ($start < $recommendCount) {
                    //需要用非推荐课程补全limit
                    $fixedStart = 0;
                    $fixedLimit = $limit - ($recommendCount - $start);
                } else {
                    $fixedStart = $start - $recommendCount;
                    $fixedLimit = $limit;
                }
                $UnRecommendCourses = $this->controller->getCourseService()->searchCourses($conditions, ['createdTime' => 'desc'], $fixedStart, $fixedLimit);
                $courses = array_merge($courses, $UnRecommendCourses);
            }
        } else {
            $courses = $this->controller->getCourseService()->searchCourses($conditions, $sort, $start, $limit);
        }

        $result = [
            'start' => $start,
            'limit' => $limit,
            'total' => $total,
            'data' => $this->controller->filterCourses($courses),
        ];

        return $result;
    }

    public function getLearnedCourse()
    {
        $user = $this->controller->getUserByToken($this->request);

        if (!$user->isLogin()) {
            return $this->createErrorResponse('not_login', '您尚未登录！');
        }

        $start = (int) $this->getParam('start', 0);
        $limit = (int) $this->getParam('limit', 10);
        $total = $this->getCourseSetService()->countUserLearnCourseSets($user['id']);
        $courseSets = $this->getCourseSetService()->searchUserLearnCourseSets($user['id'], $start, $limit);

        $courseSetIds = ArrayToolkit::column($courseSets, 'id');
        $courses = $this->getCourseService()->getDefaultCoursesByCourseSetIds($courseSetIds);

        $result = [
            'start' => $start,
            'limit' => $limit,
            'total' => $total,
            'data' => $this->controller->filterCourses($courses),
        ];

        return $result;
    }

    public function getLearningCourseWithoutToken()
    {
        $userId = $this->getParam('userId');

        if (empty($userId)) {
            return $this->createErrorResponse('userId', 'userId参数错误');
        }

        $user = $this->getUserService()->getUser($userId);
        $start = (int) $this->getParam('start', 0);
        $limit = (int) $this->getParam('limit', 10);
        $total = $this->getCourseSetService()->countUserLearnCourseSets($userId);
        $courseSets = $this->getCourseSetService()->searchUserLearnCourseSets($userId, $start, $limit);

        $courseSetIds = ArrayToolkit::column($courseSets, 'id');
        $courses = $this->getCourseService()->getDefaultCoursesByCourseSetIds($courseSetIds);

        $count = $this->controller->getTaskResultService()->countTaskResults([
            'userId' => $userId,
        ]);
        $learnStatusArray = $this->controller->getTaskResultService()->searchTaskResults([
            'userId' => $userId,
        ], [
            'finishedTime' => 'ASC',
        ], 0, $count);

        $tasks = $this->controller->getTaskService()->findTasksByIds(ArrayToolkit::column($learnStatusArray, 'courseTaskId'));

        $tempCourse = [];

        foreach ($courses as $key => $course) {
            $tempCourse[$course['id']] = $course;
        }

        foreach ($tasks as $key => $task) {
            $courseId = $task['courseId'];

            if (isset($tempCourse[$courseId])) {
                $tempCourse[$courseId]['lastLessonTitle'] = $task['title'];
            }
        }

        $result = [
            'start' => $start,
            'limit' => $limit,
            'total' => $total,
            'data' => (1 == $user['destroyed']) ? [] : $this->controller->filterCourses(array_values($tempCourse)),
        ];

        return $result;
    }

    public function getUserTeachCourse()
    {
        $userId = $this->getParam('userId', 0);
        $start = $this->getParam('start', 0);
        $limit = $this->getParam('limit', 10);

        $conditions = [
            'status' => 'published',
            'parentId' => 0,
        ];

        $total = $this->controller->getCourseSetService()->countUserTeachingCourseSets($userId, $conditions);

        $courseSets = $this->getCourseSetService()->searchUserTeachingCourseSets(
            $userId,
            $conditions,
            $start,
            $limit
        );

        $courseSetIds = ArrayToolkit::column($courseSets, 'id');
        $courses = $this->getCourseService()->getDefaultCoursesByCourseSetIds($courseSetIds);

        return [
            'start' => $start,
            'total' => $total,
            'limit' => $limit,
            'data' => $this->controller->filterCourses($courses),
        ];
    }

    public function getLearnStatus()
    {
        $courseId = $this->getParam('courseId');
        $user = $this->controller->getUserByToken($this->request);

        if (!$user->isLogin()) {
            return $this->createErrorResponse('not_login', '您尚未登录！');
        }

        $course = $this->controller->getCourseService()->getCourse($courseId);
        $learnStatus = $this->controller->getCourseService()->getUserLearnLessonStatuses($user['id'], $courseId);

        if (!empty($course)) {
            $member = $this->controller->getCourseMemberService()->getCourseMember($course['id'], $user['id']);
            $progress = $this->calculateUserLearnProgress($course, $member);
        } else {
            $course = [];
            $progress = [];
        }

        foreach ($learnStatus as $key => $value) {
            if ('finished' == $value) {
                unset($learnStatus[$key]);
            }
        }

        $keys = array_keys($learnStatus);
        $lessonId = end($keys);
        $lesson = $this->controller->getCourseService()->getCourseLesson($courseId, $lessonId);

        return [
            'data' => $lesson,
            'progress' => $progress,
        ];
    }

    private function calculateUserLearnProgress($course, $member)
    {
        if (0 == $course['lessonNum']) {
            return [
                'percent' => '0%',
                'number' => 0,
                'total' => 0,
            ];
        }

        $percent = intval($member['learnedNum'] / $course['lessonNum'] * 100).'%';

        return [
            'percent' => $percent,
            'number' => $member['learnedNum'],
            'total' => $course['lessonNum'],
        ];
    }

    private function checkMemberStatus($member)
    {
        if ($member) {
            $deadline = $member['deadline'];

            if (0 == $deadline) {
                return $member;
            }

            $remain = $deadline - time();

            if ($remain <= 0) {
                $member['deadline'] = -1;
            } else {
                $member['deadline'] = $remain;
            }
        }

        return $member;
    }

    public function getLiveCourse()
    {
        $user = $this->controller->getUserByToken($this->request);

        if (!$user->isLogin()) {
            return $this->createErrorResponse('not_login', '您尚未登录！');
        }

        $courseId = $this->getParam('courseId', 0);
        $lessonId = $this->getParam('lessonId', 0);
        $lesson = $this->controller->getCourseService()->getCourseLesson($courseId, $lessonId);
        $now = time();
        $params = [];

        $params['email'] = 'live-'.$user['id'].'@edusoho.net';
        $params['nickname'] = $user['nickname'];

        $params['sign'] = "c{$lesson['courseId']}u{$user['id']}t{$now}";
        $params['sign'] .= 's'.$this->makeSign($params['sign']);

        $params['liveId'] = $lesson['mediaId'];
        $params['provider'] = $lesson['liveProvider'];
        $params['role'] = 'student';

        $params['user'] = $params['email'];

        if ($user->isLogin()) {
            $params['userId'] = $user['id'];
        }

        $client = new EdusohoLiveClient();

        if (isset($lesson['replayStatus']) && 'generated' == $lesson['replayStatus']) {
            $result = $client->entryReplay($params, 'root');
        } else {
            $result = $client->getRoomUrl($params, 'root');
        }

        return [
            'data' => [
                'lesson' => $lesson,
                'result' => $result,
            ],
        ];
    }

    protected function makeSign($string)
    {
        $secret = $this->controller->getContainer()->getParameter('secret');

        return md5($string.$secret);
    }

    public function getLiveCourses()
    {
        $user = $this->controller->getUserByToken($this->request);

        if (!$user->isLogin()) {
            return $this->createErrorResponse('not_login', '您尚未登录！');
        }

        $start = (int) $this->getParam('start', 0);
        $limit = (int) $this->getParam('limit', 10);

        $courses = $this->controller->getCourseService()->findUserLearningCourses(
            $user['id'],
            $start,
            1000,
            ['type' => 'live']
        );

        $courseIds = ArrayToolkit::column($courses, 'id');

        $conditions = [
            'status' => 'published',
            'startTime_GE' => time(),
            'courseIds' => $courseIds,
            'type' => 'live',
        ];

        $count = $this->getTaskService()->countTasks($conditions);

        $lessons = $this->getTaskService()->searchTasks(
            $conditions,
            ['startTime' => 'ASC'],
            $start,
            $limit
        );

        $newCourses = [];

        $courses = ArrayToolkit::index($courses, 'id');

        if (!empty($courses)) {
            foreach ($lessons as $key => &$lesson) {
                if (empty($courses[$lesson['courseId']])) {
                    continue;
                }
                $newCourses[$key] = $courses[$lesson['courseId']];
                $newCourses[$key]['liveLessonTitle'] = $lesson['title'];
                $newCourses[$key]['liveStartTime'] = date('c', $lesson['startTime']);
                $newCourses[$key]['liveEndTime'] = date('c', $lesson['endTime']);
                unset($courses[$lesson['courseId']]);
            }

            foreach ($courses as $key => &$course) {
                $course['liveLessonTitle'] = '';
                $course['liveStartTime'] = '';
                $course['liveEndTime'] = '';
            }
        }

        $newCourses = array_merge($newCourses, $courses);
        $resultLiveCourses = $this->controller->filterCourses(array_values($newCourses));

        return [
            'start' => $start + count($resultLiveCourses),
            'limit' => $limit,
            'data' => $resultLiveCourses, ];
    }

    public function hitThread()
    {
        $courseId = $this->getParam('courseId', 0);
        $threadId = $this->getParam('threadId', 0);

        if (empty($courseId) || empty($threadId)) {
            return $this->createErrorResponse('wrong threadId', '问答不存在或已删除');
        }

        return $this->controller->getThreadService()->hitThread($courseId, $threadId);
    }

    public function getAllLiveCourses()
    {
        $start = $this->getParam('start', 0);
        $limit = $this->getParam('limit', 10);
        $condition = [
            'parentId' => 0,
            'status' => 'published',
            'type' => 'live',
        ];

        $total = $this->controller->getCourseService()->searchCourseCount($condition);
        $liveCourses = $this->controller->getCourseService()->searchCourses($condition, 'lastest', $start, $limit);

        $liveCourses = array_map(
            function ($liveCourse) {
                return $liveCourse;
            },
            $liveCourses
        );

        $result = [
            'start' => $start,
            'limit' => $limit,
            'total' => $total,
            'data' => $this->controller->filterCourses($liveCourses), ];

        return $result;
    }

    public function getModifyInfo()
    {
        $user = $this->controller->getUserByToken($this->request);

        if (!$user->isLogin()) {
            return $this->createErrorResponse('not_login', '您尚未登录！');
        }

        $courseSetting = $this->getSettingService()->get('course', []);

        $userinfoFields = [];
        $userInfo = $this->getUserService()->getUserProfile($user['id']);

        foreach ($courseSetting['userinfoFields'] as $key) {
            $field = [];

            switch ($key) {
                case 'truename':
                    ;
                    $field = [
                        'name' => $key,
                        'title' => '真实姓名',
                    ];
                    break;
                case 'mobile':
                    ;
                    $field = [
                        'name' => $key,
                        'title' => '手机',
                    ];
                    break;
                case 'qq':
                    ;
                    $field = [
                        'name' => $key,
                        'title' => 'QQ',
                    ];
                    break;
                case 'job':
                    ;
                    $field = [
                        'name' => $key,
                        'title' => '职业',
                    ];
                    break;
                case 'gender':
                    ;
                    $field = [
                        'name' => $key,
                        'title' => '性别',
                    ];
                    break;
                case 'idcard':
                    ;
                    $field = [
                        'name' => $key,
                        'title' => '身份证',
                    ];
                    break;
                case 'weibo':
                    ;
                    $field = [
                        'name' => $key,
                        'title' => '微博',
                    ];
                    break;
                case 'weixin':
                    ;
                    $field = [
                        'name' => $key,
                        'title' => '微信',
                    ];
                    break;
            }

            $field['content'] = $userInfo[$key];
            $userinfoFields[] = $field;
        }

        return [
            'buy_fill_userinfo' => $courseSetting['buy_fill_userinfo'] ? true : false,
            'modifyInfos' => $userinfoFields,
        ];
    }

    public function updateModifyInfo()
    {
        $fields = $this->request->request->all();
        $user = $this->controller->getUserByToken($this->request);

        if (!$user->isLogin()) {
            return $this->createErrorResponse('not_login', '您尚未登录！');
        }

        $course = $this->getCourseService()->getCourse($fields['targetId']);

        if (empty($course)) {
            return $this->createErrorResponse('error', '课程不存在，不能购买。');
        }

        $userInfo = ArrayToolkit::parts(
            $fields,
            [
                'truename',
                'mobile',
                'qq',
                'company',
                'weixin',
                'weibo',
                'idcard',
                'gender',
                'job',
                'intField1',
                'intField2',
                'intField3',
                'intField4',
                'intField5',
                'floatField1',
                'floatField2',
                'floatField3',
                'floatField4',
                'floatField5',
                'dateField1',
                'dateField2',
                'dateField3',
                'dateField4',
                'dateField5',
                'varcharField1',
                'varcharField2',
                'varcharField3',
                'varcharField4',
                'varcharField5',
                'varcharField10',
                'varcharField6',
                'varcharField7',
                'varcharField8',
                'varcharField9',
                'textField1',
                'textField2',
                'textField3',
                'textField4',
                'textField5',
                'textField6',
                'textField7',
                'textField8',
                'textField9',
                'textField10',
            ]
        );

        try {
            $userInfo = $this->getUserService()->updateUserProfile($user['id'], $userInfo);
        } catch (\Exception $e) {
            return $this->createErrorResponse('error', $e->getMessage());
        }

        return true;
    }

    protected function getVipFacadeService()
    {
        return $this->controller->getService('VipPlugin:Vip:VipFacadeService');
    }

    protected function getCourseService()
    {
        return $this->controller->getService('Course:CourseService');
    }

    protected function getCourseSetService()
    {
        return $this->controller->getService('Course:CourseSetService');
    }

    protected function getCourseMemberService()
    {
        return $this->controller->getService('Course:MemberService');
    }

    protected function getTaskService()
    {
        return $this->controller->getService('Task:TaskService');
    }

    protected function getVipLevelService()
    {
        return $this->controller->getService('VipPlugin:Vip:LevelService');
    }

    protected function getDiscountService()
    {
        return $this->controller->getService('DiscountPlugin:Discount:DiscountService');
    }

    protected function updateMemberLastViewTime($member)
    {
        if (!empty($member)) {
            $fields['lastViewTime'] = time();
            $this->controller->getCourseMemberService()->updateMember($member['id'], $fields);
        }
    }

    private function isUserVipExpire($course, $member)
    {
        if (!($this->controller->isinstalledPlugin('Vip') && $this->controller->setting('vip.enabled'))) {
            return false;
        }

        $user = $this->controller->getUserByToken($this->request);
        if ($user->isAdmin()) {
            return false;
        }

        //班级课程、不是班级成员不处理
        if ($course['parentId'] > 0 || !$member || 'teacher' === $member['role']) {
            return false;
        }

        //老VIP加入接口加入进来的用户
        if ($course['vipLevelId'] > 0 && ((0 == $member['orderId'] && 0 == $member['levelId']) || $member['levelId'] > 0)) {
            $userVipStatus = $this->getVipService()->checkUserInMemberLevel(
                $member['userId'],
                $course['vipLevelId']
            );

            return 'ok' !== $userVipStatus;
        }

        return false;
    }

    private function getVipService()
    {
        return $this->controller->getService('VipPlugin:Vip:VipService');
    }

    /**
     * @return FavoriteService
     */
    public function getFavoriteService()
    {
        return $this->controller->getService('Favorite:FavoriteService');
    }
}
