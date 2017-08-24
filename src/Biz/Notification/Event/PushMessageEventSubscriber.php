<?php

namespace Biz\Notification\Event;

use Biz\Classroom\Service\ClassroomService;
use Biz\CloudData\Service\CloudDataService;
use Biz\CloudPlatform\IMAPIFactory;
use Biz\CloudPlatform\QueueJob\PushJob;
use Biz\CloudPlatform\Service\PushService;
use Biz\CloudPlatform\Service\SearchService;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\Group\Service\GroupService;
use Biz\IM\Service\ConversationService;
use Biz\System\Service\SettingService;
use Biz\Task\Service\TaskService;
use Biz\Testpaper\Service\TestpaperService;
use Biz\User\Service\UserService;
use Codeages\Biz\Framework\Queue\Service\QueueService;
use Codeages\Biz\Framework\Scheduler\Service\SchedulerService;
use Topxia\Api\Util\MobileSchoolUtil;
use Codeages\Biz\Framework\Event\Event;
use Codeages\PluginBundle\Event\EventSubscriber;

class PushMessageEventSubscriber extends EventSubscriber
{
    public static function getSubscribedEvents()
    {
        return array(
            'article.create' => 'onArticleCreate',
            //资讯在创建的时候状态就是已发布的
            'article.publish' => 'onArticleCreate',

            'user.registered' => 'onUserCreate',
            'user.unlock' => 'onUserCreate',
            'user.lock' => 'onUserDelete',
            'user.update' => 'onUserUpdate',
            'user.change_nickname' => 'onUserUpdate',
            'user.follow' => 'onUserFollow',
            'user.unfollow' => 'onUserUnFollow',

            'classroom.join' => 'onClassroomJoin',
            'classroom.quit' => 'onClassroomQuit',

            'article.update' => 'onArticleUpdate',
            'article.trash' => 'onArticleDelete',
            'article.unpublish' => 'onArticleDelete',
            'article.delete' => 'onArticleDelete',

            //云端不分thread、courseThread、groupThread，统一处理成字段：id, target,relationId, title, content, content, postNum, hitNum, updateTime, createdTime
            'thread.create' => 'onThreadCreate',
            'thread.update' => 'onThreadUpdate',
            'thread.delete' => 'onThreadDelete',
            'course.thread.create' => 'onCourseThreadCreate',
            'course.thread.update' => 'onCourseThreadUpdate',
            'course.thread.delete' => 'onCourseThreadDelete',
            'group.thread.create' => 'onGroupThreadCreate',
            'group.thread.open' => 'onGroupThreadOpen',
            'group.thread.update' => 'onGroupThreadUpdate',
            'group.thread.delete' => 'onGroupThreadDelete',

            'thread.post.create' => 'onThreadPostCreate',
            'thread.post.delete' => 'onThreadPostDelete',
            'course.thread.post.create' => 'onCourseThreadPostCreate',
            'course.thread.post.update' => 'onCourseThreadPostUpdate',
            'course.thread.post.delete' => 'onCourseThreadPostDelete',
            'group.thread.post.create' => 'onGroupThreadPostCreate',
            'group.thread.post.delete' => 'onGroupThreadPostDelete',

            'announcement.create' => 'onAnnouncementCreate',

            //兼容模式，courseSet映射到course
            'course-set.publish' => 'onCourseCreate',
            'course-set.update' => 'onCourseUpdate',
            'course-set.delete' => 'onCourseDelete',
            'course-set.close' => 'onCourseDelete',

            //教学计划购买
            'course.join' => 'onCourseJoin',
            'course.quit' => 'onCourseQuit',

            //兼容模式，task映射到lesson
            'course.task.publish' => 'onCourseLessonCreate',
            'course.task.unpublish' => 'onCourseLessonDelete',
            'course.task.update' => 'onCourseLessonUpdate',
            'course.task.delete' => 'onCourseLessonDelete',

            'coupon.update' => 'onCouponUpdate',
        );
    }

    /**
     * Article相关.
     *
     * @PushService
     * @SearchService
     */
    public function onArticleCreate(Event $event)
    {
        $article = $event->getSubject();

        $schoolUtil = new MobileSchoolUtil();

        $articleApp = $schoolUtil->getArticleApp();
        $articleApp['avatar'] = $this->getAssetUrl($articleApp['avatar']);
        $article['app'] = $articleApp;

        $imSetting = $this->getSettingService()->get('app_im', array());
        $article['convNo'] = isset($imSetting['convNo']) && !empty($imSetting['convNo']) ? $imSetting['convNo'] : '';
        $article = $this->convertArticle($article);

        $from = array(
            'id' => $article['app']['id'],
            'type' => $article['app']['code'],
        );

        $to = array(
            'type' => 'global',
            'convNo' => empty($article['convNo']) ? '' : $article['convNo'],
        );

        $body = array(
            'type' => 'news.create',
            'id' => $article['id'],
            'title' => $article['title'], //@todo 咨询的文案是什么
            'image' => $article['thumb'],
            'content' => $this->plainText($article['body'], 50),
        );

        $this->createPushJob($from, $to, $body);

        //@TODO SearchJob

    }

    /**
     * Announcement相关.
     *
     * @PushService
     */
    public function onAnnouncementCreate(Event $event)
    {
        $announcement = $event->getSubject();

        $target = $this->getTarget($announcement['targetType'], $announcement['targetId']);
        $announcement['target'] = $target;

        $from = array(
            'type' => $target['type'],
            'id' => $target['id'],
        );

        $to = array(
            'type' => $target['type'],
            'id' => $target['id'],
            'convNo' => empty($target['convNo']) ? '' : $target['convNo'],
        );

        $body = array(
            'id' => $announcement['id'],
            'type' => 'announcement.create',
            'title' => $this->plainText($announcement['content'], 50),
        );

        $this->createPushJob($from, $to, $body);

        //@TODO SearchJob

    }

    /**
     * Thread相关.
     *
     * @PushService
     * @SearchService
     */
    public function onThreadCreate(Event $event)
    {
        $thread = $event->getSubject();
        $thread = $this->convertThread($thread, 'thread.create');

        if ($thread['target']['type'] != 'course' || $thread['type'] != 'question') {
            return ;
        }

        $from = array(
            'type' => $thread['target']['type'],
            'id' => $thread['target']['id'],
        );

        $to = array(
            'type' => 'user',
            'convNo' => empty($target['convNo']) ? '' : $target['convNo'],
        );

        $body = array(
            'type' => 'question.created',
            'threadId' => $thread['id'],
            'courseId' => $thread['target']['id'],
            'lessonId' => $thread['relationId'],
            'questionCreatedTime' => $thread['createdTime'],
            'questionTitle' => $thread['title'],
            'title' => "{$thread['target']['title']}有新问题"
        );
        foreach ($thread['target']['teacherIds'] as $teacherId) {
            $to['id'] = $teacherId;
            $this->createPushJob($from, $to, $body);
        }

        //@TODO searchJob
    }

    public function onGroupThreadCreate(Event $event)
    {
        $thread = $event->getSubject();
        $thread = $this->convertThread($thread, 'group.thread.create');

        if ($thread['target']['type'] != 'course' || $thread['type'] != 'question') {
            return ;
        }

        $from = array(
            'type' => $thread['target']['type'],
            'id' => $thread['target']['id'],
        );

        $to = array(
            'type' => 'user',
            'convNo' => empty($target['convNo']) ? '' : $target['convNo'],
        );
        $body = array(
            'type' => 'question.created',
            'threadId' => $thread['id'],
            'courseId' => $thread['target']['id'],
            'lessonId' => $thread['relationId'],
            'questionCreatedTime' => $thread['createdTime'],
            'questionTitle' => $thread['title'],
            'title' => "{$thread['target']['title']}有新问题"
        );
        foreach ($thread['target']['teacherIds'] as $teacherId) {
            $to['id'] = $teacherId;
            $this->createPushJob($from, $to, $body);
        }

        //@TODO searchJob
    }

    public function onCourseThreadCreate(Event $event)
    {
        $thread = $event->getSubject();
        $thread = $this->convertThread($thread, 'course.thread.create');

        $this->getPushService()->pushThreadCreate($thread);
        $this->getSearchService()->notifyThreadCreate($thread);

        if ($thread['target']['type'] != 'course' || $thread['type'] != 'question') {
            return;
        }

        $from = array(
            'type' => $thread['target']['type'],
            'id' => $thread['target']['id'],
        );

        $to = array(
            'type' => 'user',
            'convNo' => empty($thread['target']['convNo']) ? '' : $thread['target']['convNo'],
        );

        $body = array(
            'type' => 'question.created',
            'threadId' => $thread['id'],
            'courseId' => $thread['target']['id'],
            'lessonId' => $thread['relationId'],
            'questionCreatedTime' => $thread['createdTime'],
            'questionTitle' => $thread['title'],
            'title' => "{$thread['target']['title']} 有新问题",
        );

        foreach (array_values($thread['target']['teacherIds']) as $i => $teacherId) {
            if ($i >= 3) {
                break;//TODO 这里为什么是3
            }
            $to['id'] = $teacherId;

            $this->createPushJob($from, $to, $body);
        }

        //@todo search
    }

    /**
     * ThreadPost相关.
     *
     * @PushService
     */
    public function onThreadPostCreate(Event $event)
    {
        $threadPost = $event->getSubject();
        $threadPost = $this->convertThreadPost($threadPost, 'thread.post.create');
        if ($threadPost['target']['type'] != 'course' || empty($threadPost['target']['teacherIds'])) {
            return ;
        }

        if ($threadPost['thread']['type'] != 'question') {
            return ;
        }

        foreach ($threadPost['target']['teacherIds'] as $teacherId) {
            if ($teacherId != $threadPost['userId']) {
                continue;
            }

            $from = array(
                'type' => $threadPost['target']['type'],
                'id' => $threadPost['target']['id'],
                'image' => $threadPost['target']['image'],
            );

            $to = array(
                'type' => 'user',
                'id' => $threadPost['thread']['userId'],
                'convNo' => empty($threadPost['target']['convNo']) ? '' : $threadPost['target']['convNo'],
            );

            $body = array(
                'type' => 'question.answered',
                'threadId' => $threadPost['threadId'],
                'courseId' => $threadPost['target']['id'],
                'lessonId' => $threadPost['thread']['relationId'],
                'questionCreatedTime' => $threadPost['thread']['createdTime'],
                'questionTitle' => $threadPost['thread']['title'],
                'postContent' => $threadPost['content'],
                'title' => "{$threadPost['thread']['title']}有新回复"
            );

            $this->createPushJob($from, $to, $body);
        }
    }

    public function onCourseThreadPostCreate(Event $event)
    {
        $threadPost = $event->getSubject();
        $threadPost = $this->convertThreadPost($threadPost, 'course.thread.post.create');

        if ($threadPost['target']['type'] != 'course' || empty($threadPost['target']['teacherIds'])) {
            return ;
        }

        if ($threadPost['thread']['type'] != 'question') {
            return ;
        }

        foreach ($threadPost['target']['teacherIds'] as $teacherId) {
            if ($teacherId != $threadPost['userId']) {
                continue;
            }

            $from = array(
                'type' => $threadPost['target']['type'],
                'id' => $threadPost['target']['id'],
                'image' => $threadPost['target']['image'],
            );

            $to = array(
                'type' => 'user',
                'id' => $threadPost['thread']['userId'],
                'convNo' => empty($threadPost['target']['convNo']) ? '' : $threadPost['target']['convNo'],
            );

            $body = array(
                'type' => 'question.answered',
                'threadId' => $threadPost['threadId'],
                'courseId' => $threadPost['target']['id'],
                'lessonId' => $threadPost['thread']['relationId'],
                'questionCreatedTime' => $threadPost['thread']['createdTime'],
                'questionTitle' => $threadPost['thread']['title'],
                'postContent' => $threadPost['content'],
            );

            $this->createPushJob($from, $to, $body);
        }

    }

    public function onGroupThreadPostCreate(Event $event)
    {
        $post = $event->getSubject();
        $post = $this->convertThreadPost($post, 'group.thread.post.create');

        if ($post['target']['type'] != 'course' || empty($post['target']['teacherIds'])) {
            return ;
        }

        if ($post['thread']['type'] != 'question') {
            return ;
        }

        foreach ($post['target']['teacherIds'] as $teacherId) {
            if ($teacherId != $post['userId']) {
                continue;
            }

            $from = array(
                'type' => $post['target']['type'],
                'id' => $post['target']['id'],
            );

            $to = array(
                'type' => 'user',
                'id' => $post['thread']['userId'],
                'convNo' => empty($post['target']['convNo']) ? '' : $post['target']['convNo'],
            );

            $body = array(
                'type' => 'question.answered',
                'threadId' => $post['threadId'],
                'courseId' => $post['target']['id'],
                'lessonId' => $post['thread']['relationId'],
                'questionCreatedTime' => $post['thread']['createdTime'],
                'questionTitle' => $post['thread']['title'],
                'postContent' => $post['content'],
                'title' => "{$post['thread']['title']} 有新回复",
            );

            $this->createPushJob($from, $to, $body);
        }
    }

    public function onCourseJoin(Event $event)
    {
        $course = $event->getSubject();
        $userId = $event->getArgument('userId');
        $member = $event->getArgument('member');

        if (!empty($course['parentId'])) {
            return;
        }

        $member['course'] = $this->convertCourse($course);
        $member['user'] = $this->convertUser($this->getUserService()->getUser($userId));
//
//        $from = array(
//            'type' => 'course',
//            'id' => $course['id'],
//        );
//
//        $to = array(
//            'type' => 'user',
//            'id' =>
//        );
    }


    public function onTestpaperReviewed(Event $event)
    {
        //@TODO 暂时没有，待添加
    }

    public function onHomeworkCheck(Event $event)
    {
        //@TODO 暂时没有，待添加
    }


    private function createPushJob($from, $to, $body)
    {
        $pushJob = new PushJob(array(
            'from' => $from,
            'to' => $to,
            'body' => $body
        ));

        $this->getQueueService()->pushJob($pushJob);
    }

    protected function pushCloud($eventName, array $data, $level = 'normal')
    {
        return $this->getCloudDataService()->push('school.'.$eventName, $data, time(), $level);
    }

    public function onCouponUpdate(Event $event)
    {
        $coupon = $event->getSubject();
        if ($coupon['status'] != 'receive') {
            return;
        }

        $this->getPushService()->pushCouponReceived($coupon);
    }

    public function onUserUpdate(Event $event)
    {
        $context = $event->getSubject();
        if (!isset($context['user'])) {
            return;
        }
        $user = $context['user'];
        $profile = $this->getUserService()->getUserProfile($user['id']);
        $user = $this->convertUser($user, $profile);
        $this->getSearchService()->notifyUserUpdate($user);
    }

    public function onUserFollow(Event $event)
    {
        $friend = $event->getSubject();
        $user = $this->getBiz()->offsetGet('user');
        $this->getPushService()->pushUserFollow($user, $friend);
    }

    public function onUserUnFollow(Event $event)
    {
        $friend = $event->getSubject();
        $user = $this->getBiz()->offsetGet('user');
        $this->getPushService()->pushUserUnFollow($user, $friend);
    }

    public function onUserCreate(Event $event)
    {
        $user = $event->getSubject();
        $profile = $this->getUserService()->getUserProfile($user['id']);
        $user = $this->convertUser($user, $profile);
        $this->getSearchService()->notifyUserCreate($user);
    }

    public function onUserDelete(Event $event)
    {
        $user = $event->getSubject();
        $profile = $this->getUserService()->getUserProfile($user['id']);
        $user = $this->convertUser($user, $profile);
        $this->getSearchService()->notifyUserDelete($user);
    }

    protected function convertUser($user, $profile = array())
    {
        // id, nickname, title, roles, point, avatar(最大那个), about, updatedTime, createdTime
        $converted = array();
        $converted['id'] = $user['id'];
        $converted['nickname'] = $user['nickname'];
        $converted['title'] = $user['title'];

        if (!is_array($user['roles'])) {
            $user['roles'] = explode('|', $user['roles']);
        }

        $converted['roles'] = in_array('ROLE_TEACHER', $user['roles']) ? 'teacher' : 'student';
        $converted['point'] = $user['point'];
        $converted['avatar'] = $this->getFileUrl($user['largeAvatar']);
        $converted['about'] = empty($profile['about']) ? '' : $profile['about'];
        $converted['updatedTime'] = $user['updatedTime'];
        $converted['createdTime'] = $user['createdTime'];

        return $converted;
    }

    public function onCoursePublish(Event $event)
    {
        $course = $event->getSubject();
        $course = $this->convertCourse($course);
        $this->getSearchService()->notifyCourseCreate($course);
    }

    public function onCourseCreate(Event $event)
    {
        $course = $event->getSubject();
        $course = $this->convertCourse($course);
        $this->getSearchService()->notifyCourseCreate($course);
    }

    public function onCourseUpdate(Event $event)
    {
        $course = $event->getSubject();
        $course = $this->convertCourse($course);
        $this->getSearchService()->notifyCourseUpdate($course);
    }

    public function onCourseDelete(Event $event)
    {
        $course = $event->getSubject();
        $course = $this->convertCourse($course);

        $this->getSearchService()->notifyCourseDelete($course);
    }

    public function onCourseQuit(Event $event)
    {
        $course = $event->getSubject();
        $userId = $event->getArgument('userId');
        $member = $event->getArgument('member');

        if (!empty($course['parentId'])) {
            return;
        }

        $member['course'] = $this->convertCourse($course);
        $member['user'] = $this->convertUser($this->getUserService()->getUser($userId));

        $this->getPushService()->pushCourseQuit($member);
    }

    protected function convertCourse($course)
    {
        $course['smallPicture'] = isset($course['cover']['small']) ? $this->getFileUrl($course['cover']['small']) : '';
        $course['middlePicture'] = isset($course['cover']['middle']) ? $this->getFileUrl($course['cover']['middle']) : '';
        $course['largePicture'] = isset($course['cover']['large']) ? $this->getFileUrl($course['cover']['large']) : '';
        $course['about'] = isset($course['summary']) ? $this->convertHtml($course['summary']) : '';

        return $course;
    }

    protected function convertOpenCourse($openCourse)
    {
        $openCourse['smallPicture'] = $this->getFileUrl($openCourse['smallPicture']);
        $openCourse['middlePicture'] = $this->getFileUrl($openCourse['middlePicture']);
        $openCourse['largePicture'] = $this->getFileUrl($openCourse['largePicture']);
        $openCourse['about'] = $this->convertHtml($openCourse['about']);

        return $openCourse;
    }

    /**
     * CourseLesson相关.
     *
     * @SearchService
     */
    public function onCourseLessonCreate(Event $event)
    {
        $lesson = $event->getSubject();

        $mobileSetting = $this->getSettingService()->get('mobile');

        if ((!isset($mobileSetting['enable']) || $mobileSetting['enable']) && $lesson['type'] == 'live') {
            //这个任务要去关注一下也得改
            $this->createJob($lesson);
        }

        $this->getSearchService()->notifyTaskCreate($lesson);
    }

    public function onCourseLessonUpdate(Event $event)
    {
        $lesson = $event->getSubject();
        $oldTask = $event->getArguments();
        $mobileSetting = $this->getSettingService()->get('mobile');

        $shouldReCreatePushJOB = $lesson['type'] == 'live' && isset($oldTask['startTime']) && $oldTask['startTime'] != $lesson['startTime'] && (!isset($mobileSetting['enable']) || $mobileSetting['enable']);
        if ($shouldReCreatePushJOB) {
            $this->deleteJob($lesson);

            if ($lesson['status'] == 'published') {
                //这个任务要关注，得改
                $this->createJob($lesson);
            }
        }

        $this->getSearchService()->notifyTaskUpdate($lesson);
    }

    public function onCourseLessonDelete(Event $event)
    {
        $context = $event->getSubject();
        if (isset($context['lesson'])) {
            $lesson = $context['lesson'];
        } else {
            $lesson = $context;
        }

        $this->deleteJob($lesson);

        $this->getSearchService()->notifyTaskDelete($lesson);
    }

    public function onClassroomJoin(Event $event)
    {
        $classroom = $event->getSubject();
        $userId = $event->getArgument('userId');
        $member = $event->getArgument('member');

        $member['classroom'] = $this->convertClassroom($classroom);
        $member['user'] = $this->convertUser($this->getUserService()->getUser($userId));

        $this->getPushService()->pushClassroomJoin($member);
    }

    public function onClassroomQuit(Event $event)
    {
        $classroom = $event->getSubject();
        $userId = $event->getArgument('userId');
        $member = $event->getArgument('member');

        $member['classroom'] = $this->convertClassroom($classroom);
        $member['user'] = $this->convertUser($this->getUserService()->getUser($userId));

        $this->getPushService()->pushClassroomQuit($member);
    }

    protected function convertClassroom($classroom)
    {
        $classroom['smallPicture'] = $this->getFileUrl($classroom['smallPicture']);
        $classroom['middlePicture'] = $this->getFileUrl($classroom['middlePicture']);
        $classroom['largePicture'] = $this->getFileUrl($classroom['largePicture']);
        $classroom['about'] = $this->convertHtml($classroom['about']);

        return $classroom;
    }

    /**
     * @param Event $event
     * @SearchService
     */
    public function onArticleUpdate(Event $event)
    {
        $article = $event->getSubject();
        $article = $this->convertArticle($article);
        $this->getSearchService()->notifyArticleUpdate($article);
    }

    public function onArticleDelete(Event $event)
    {
        $article = $event->getSubject();
        $article = $this->convertArticle($article);
        $this->getSearchService()->notifyArticleDelete($article);
    }

    protected function convertArticle($article)
    {
        $article['thumb'] = $this->getFileUrl($article['thumb']);
        $article['originalThumb'] = $this->getFileUrl($article['originalThumb']);
        $article['picture'] = $this->getFileUrl($article['picture']);
        $article['body'] = $article['title'];

        return $article;
    }

    public function onGroupThreadOpen(Event $event)
    {
        $thread = $event->getSubject();
        $thread = $this->convertThread($thread, 'group.thread.open');
        $this->getPushService()->pushThreadCreate($thread);
        $this->getSearchService()->notifyThreadCreate($thread);
    }

    /**
     * @param Event $event
     * @SearchService
     */
    public function onThreadUpdate(Event $event)
    {
        $thread = $event->getSubject();
        $thread = $this->convertThread($thread, 'thread.update');
        $this->getSearchService()->notifyThreadUpdate($thread);
    }

    public function onCourseThreadUpdate(Event $event)
    {
        $thread = $event->getSubject();
        $thread = $this->convertThread($thread, 'course.thread.update');
        $this->getSearchService()->notifyThreadUpdate($thread);
    }

    public function onGroupThreadUpdate(Event $event)
    {
        $thread = $event->getSubject();
        $thread = $this->convertThread($thread, 'group.thread.update');
        $this->getSearchService()->notifyThreadUpdate($thread);
    }

    public function onThreadDelete(Event $event)
    {
        $thread = $event->getSubject();
        $thread = $this->convertThread($thread, 'thread.delete');
        $this->getSearchService()->notifyThreadDelete($thread);
    }

    public function onCourseThreadDelete(Event $event)
    {
        $thread = $event->getSubject();
        $thread = $this->convertThread($thread, 'course.thread.delete');
        $this->getSearchService()->notifyThreadDelete($thread);
    }

    public function onGroupThreadDelete(Event $event)
    {
        $thread = $event->getSubject();
        $thread = $this->convertThread($thread, 'group.thread.delete');
        $this->getSearchService()->notifyThreadDelete($thread);
    }

    public function onGroupThreadClose(Event $event)
    {
        $thread = $event->getSubject();
        $thread = $this->convertThread($thread, 'group.thread.close');
        $this->getSearchService()->notifyThreadDelete($thread);
    }

    protected function convertThread($thread, $eventName)
    {
        if (strpos($eventName, 'course') === 0) {
            $thread['targetType'] = 'course';
            $thread['targetId'] = $thread['courseId'];
            $thread['relationId'] = $thread['taskId'];
        } elseif (strpos($eventName, 'group') === 0) {
            $thread['targetType'] = 'group';
            $thread['targetId'] = $thread['groupId'];
            $thread['relationId'] = 0;
        }

        // id, target, relationId, title, content, postNum, hitNum, updateTime, createdTime
        $converted = array();

        $converted['id'] = $thread['id'];
        $converted['target'] = $this->getTarget($thread['targetType'], $thread['targetId']);
        $converted['relationId'] = $thread['relationId'];
        $converted['type'] = empty($thread['type']) ? 'none' : $thread['type'];
        $converted['userId'] = empty($thread['userId']) ? 0 : $thread['userId'];
        $converted['title'] = $thread['title'];
        $converted['content'] = $this->convertHtml($thread['content']);
        $converted['postNum'] = $thread['postNum'];
        $converted['hitNum'] = $thread['hitNum'];
        $converted['updateTime'] = isset($thread['updateTime']) ? $thread['updateTime'] : $thread['updatedTime'];
        $converted['createdTime'] = $thread['createdTime'];

        return $converted;
    }

    //下面的四个搜没有对应的event
    public function onCourseThreadPostUpdate(Event $event)
    {
        $threadPost = $event->getSubject();
        $this->pushCloud('thread_post.update', $this->convertThreadPost($threadPost, 'course.thread.post.update'));
    }

    public function onThreadPostDelete(Event $event)
    {
        $threadPost = $event->getSubject();
        $this->pushCloud('thread_post.delete', $this->convertThreadPost($threadPost, 'thread.post.delete'));
    }

    public function onCourseThreadPostDelete(Event $event)
    {
        $threadPost = $event->getSubject();
        $this->pushCloud('thread_post.delete', $this->convertThreadPost($threadPost, 'course.thread.post.delete'));
    }

    public function onGroupThreadPostDelete(Event $event)
    {
        $threadPost = $event->getSubject();
        $this->pushCloud('thread_post.delete', $this->convertThreadPost($threadPost, 'group.thread.post.delete'));
    }

    protected function convertThreadPost($threadPost, $eventName)
    {
        if (strpos($eventName, 'course') === 0) {
            $threadPost['targetType'] = 'course';
            $threadPost['targetId'] = $threadPost['courseId'];
            $threadPost['thread'] = $this->convertThread(
                $this->getThreadService('course')->getThread($threadPost['courseId'], $threadPost['threadId']),
                $eventName
            );
        } elseif (strpos($eventName, 'group') === 0) {
            $thread = $this->getThreadService('group')->getThread($threadPost['threadId']);
            $threadPost['targetType'] = 'group';
            $threadPost['targetId'] = $thread['groupId'];
            $threadPost['thread'] = $this->convertThread($thread, $eventName);
        } else {
            $threadPost['thread'] = $this->convertThread(
                $this->getThreadService()->getThread($threadPost['threadId']),
                $eventName
            );
        }

        // id, threadId, content, userId, createdTime, target, thread
        $converted = array();

        $converted['id'] = $threadPost['id'];
        $converted['threadId'] = $threadPost['threadId'];
        $converted['content'] = $this->convertHtml($threadPost['content']);
        $converted['userId'] = $threadPost['userId'];
        $converted['target'] = $this->getTarget($threadPost['targetType'], $threadPost['targetId']);
        $converted['thread'] = $threadPost['thread'];
        $converted['createdTime'] = $threadPost['createdTime'];

        return $converted;
    }

    /**
     * @param Event $event
     * @SearchService
     * 问题是没有时间触发
     */
    public function onOpenCourseCreate(Event $event)
    {
        $openCourse = $event->getSubject();
        $openCourse = $this->convertOpenCourse($openCourse);
        $this->getSearchService()->notifyOpenCourseCreate($openCourse);
    }

    public function onOpenCourseDelete(Event $event)
    {
        $openCourse = $event->getSubject();
        $openCourse = $this->convertOpenCourse($openCourse);
        $this->getSearchService()->notifyOpenCourseDelete($openCourse);
    }

    public function onOpenCourseUpdate(Event $event)
    {
        $subject = $event->getSubject();
        $course = $subject['course'];
        $course = $this->convertOpenCourse($course);
        $this->getSearchService()->notifyOpenCourseUpdate($course);
    }

    protected function getTarget($type, $id)
    {
        $target = array('type' => $type, 'id' => $id);

        switch ($type) {
            case 'course':
                $course = $this->getCourseService()->getCourse($id);
                $courseSet = $this->getCourseSetService()->getCourseSet($course['courseSetId']);
                $target['title'] = $course['title'];
                $target['image'] = empty($courseSet['cover']['small']) ? '' : $this->getFileUrl(
                    $courseSet['cover']['small']
                );
                $target['teacherIds'] = empty($course['teacherIds']) ? array() : $course['teacherIds'];
                $conv = $this->getConversationService()->getConversationByTarget($id, 'course-push');
                $target['convNo'] = empty($conv) ? '' : $conv['no'];
                break;
            case 'lesson':
                $task = $this->getTaskService()->getTask($id);
                $target['title'] = $task['title'];
                break;
            case 'classroom':
                $classroom = $this->getClassroomService()->getClassroom($id);
                $target['title'] = $classroom['title'];
                $target['image'] = $this->getFileUrl($classroom['smallPicture']);
                break;
            case 'group':
                $group = $this->getGroupService()->getGroup($id);
                $target['title'] = $group['title'];
                $target['image'] = $this->getFileUrl($group['logo']);
                break;
            case 'global':
                $schoolUtil = new MobileSchoolUtil();
                $schoolApp = $schoolUtil->getAnnouncementApp();
                $target['title'] = '网校公告';
                $target['id'] = $schoolApp['id'];
                $target['image'] = $this->getFileUrl($schoolApp['avatar']);
                $setting = $this->getSettingService()->get('app_im', array());
                $target['convNo'] = empty($setting['convNo']) ? '' : $setting['convNo'];
                break;
            default:
                // code...
                break;
        }

        return $target;
    }

    protected function getFileUrl($path)
    {
        if (empty($path)) {
            return $path;
        }

        $path = str_replace('public://', '', $path);
        $path = str_replace('files/', '', $path);
        $path = "http://{$_SERVER['HTTP_HOST']}/files/{$path}";

        return $path;
    }

    protected function getAssetUrl($path)
    {
        if (empty($path)) {
            return '';
        }

        $path = "http://{$_SERVER['HTTP_HOST']}/assets/{$path}";

        return $path;
    }

    protected function convertHtml($text)
    {
        preg_match_all('/\<img.*?src\s*=\s*[\'\"](.*?)[\'\"]/i', $text, $matches);

        if (empty($matches)) {
            return $text;
        }

        foreach ($matches[1] as $url) {
            $text = str_replace($url, $this->getFileUrl($url), $text);
        }

        return $text;
    }

    protected function plainText($text, $count)
    {
        return mb_substr($text, 0, $count, 'utf-8');
    }

    protected function createJob($lesson)
    {
        if ($lesson['startTime'] >= (time() + 60 * 60)) {
            $startJob = array(
                'name' => 'PushNotificationOneHourJob_lesson_'.$lesson['id'],
                'expression' => $lesson['startTime'] - 60 * 60,
                'class' => 'Biz\Notification\Job\PushNotificationOneHourJob',
                'args' => array(
                    'targetType' => 'lesson',
                    'targetId' => $lesson['id'],
                ),
            );
            $this->getSchedulerService()->register($startJob);
        }

        if ($lesson['type'] == 'live') {
            $startJob = array(
                'name' => 'LiveCourseStartNotifyJob_liveLesson_'.$lesson['id'],
                'expression' => $lesson['startTime'] - 10 * 60,
                'class' => 'Biz\Notification\Job\LiveLessonStartNotifyJob',
                'args' => array(
                    'targetType' => 'liveLesson',
                    'targetId' => $lesson['id'],
                ),
            );
            $this->getSchedulerService()->register($startJob);
        }
    }

    protected function deleteJob($lesson)
    {
        //这里是不是有问题？
        $this->deleteByJobName('PushNotificationOneHourJob_lesson_'.$lesson['id']);

        if ('live' == $lesson['type']) {
            $this->deleteByJobName('LiveCourseStartNotifyJob_liveLesson_'.$lesson['id']);
        }
    }

    private function deleteByJobName($jobName)
    {
        $jobs = $this->getSchedulerService()->searchJobs(array('name' => $jobName), array(), 0, PHP_INT_MAX);

        foreach ($jobs as $job) {
            $this->getSchedulerService()->deleteJob($job['id']);
        }
    }

    /**
     * @return SchedulerService
     */
    protected function getSchedulerService()
    {
        return $this->createService('Scheduler:SchedulerService');
    }

    protected function getThreadService($type = '')
    {
        if ($type == 'course') {
            return $this->createService('Course:ThreadService');
        }

        if ($type == 'group') {
            return $this->createService('Group:ThreadService');
        }

        return $this->createService('Thread:ThreadService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->createService('Task:TaskService');
    }

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->createService('Classroom:ClassroomService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    /**
     * @return TestpaperService
     *                          TODO
     */
    protected function getTestpaperService()
    {
        return $this->createService('Testpaper:TestpaperService');
    }

    /**
     * @return CloudDataService
     */
    protected function getCloudDataService()
    {
        return $this->createService('CloudData:CloudDataService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    //TODO
    protected function getHomeworkService()
    {
        return $this->createService('Homework:Homework.HomeworkService');
    }

    /**
     * @return GroupService
     */
    protected function getGroupService()
    {
        return $this->createService('Group:GroupService');
    }

    /**
     * @return ConversationService
     */
    protected function getConversationService()
    {
        return $this->createService('IM:ConversationService');
    }

    /**
     * @return PushService
     */
    protected function getPushService()
    {
        return $this->createService('CloudPlatform:PushService');
    }

    /**
     * @return SearchService
     */
    protected function getSearchService()
    {
        return $this->createService('CloudPlatform:SearchService');
    }

    /**
     * @return QueueService
     */
    protected function getQueueService()
    {
        return $this->createService('Queue:QueueService');
    }

    protected function createService($alias)
    {
        return $this->getBiz()->service($alias);
    }

    protected function pushIM($from, $to, $body)
    {
        $setting = $this->getSettingService()->get('app_im', array());
        if (empty($setting['enabled'])) {
            return;
        }

        $params = array(
            'fromId' => 0,
            'fromName' => '系统消息',
            'toName' => '全部',
            'body' => array(
                'v' => 1,
                't' => 'push',
                'b' => $body,
                's' => $from,
                'd' => $to,
            ),
            'convNo' => empty($to['convNo']) ? '' : $to['convNo'],
        );

        if ($to['type'] == 'user') {
            $params['toId'] = $to['id'];
        }

        if (empty($params['convNo'])) {
            return;
        }

        try {
            $api = IMAPIFactory::create();
            $result = $api->post('/push', $params);

            $setting = $this->getSettingService()->get('developer', array());
            if (!empty($setting['debug'])) {
                IMAPIFactory::getLogger()->debug('API RESULT', !is_array($result) ? array() : $result);
            }
        } catch (\Exception $e) {
            IMAPIFactory::getLogger()->warning('API REQUEST ERROR:'.$e->getMessage());
        }
    }
}
