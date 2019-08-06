<?php

namespace Biz\Notification\Event;

use AppBundle\Common\ArrayToolkit;
use Biz\Classroom\Service\ClassroomReviewService;
use Biz\Classroom\Service\ClassroomService;
use Biz\CloudData\Service\CloudDataService;
use Biz\CloudPlatform\IMAPIFactory;
use Biz\CloudPlatform\QueueJob\PushJob;
use Biz\CloudPlatform\QueueJob\SearchJob;
use Biz\CloudPlatform\Service\PushService;
use Biz\CloudPlatform\Service\SearchService;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\Course\Service\Impl\ReviewServiceImpl;
use Biz\Group\Service\GroupService;
use Biz\IM\Service\ConversationService;
use Biz\System\Service\SettingService;
use Biz\Task\Service\TaskService;
use Biz\Testpaper\Service\TestpaperService;
use Biz\User\Service\UserService;
use Codeages\Biz\Framework\Queue\Service\QueueService;
use Codeages\Biz\Framework\Scheduler\Service\SchedulerService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Topxia\Api\Util\MobileSchoolUtil;
use Codeages\Biz\Framework\Event\Event;
use Codeages\PluginBundle\Event\EventSubscriber;
use AppBundle\Common\StringToolkit;
use Topxia\Service\Common\ServiceKernel;
use Biz\Course\Util\CourseTitleUtils;

class PushMessageEventSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    //@TODO 将大部分没用到的接口屏蔽掉，之后要开放
    public static function getSubscribedEvents()
    {
        return array(
            'article.create' => 'onArticleCreate',
            //资讯在创建的时候状态就是已发布的
            'article.publish' => 'onArticleCreate',
            'article.update' => 'onArticleUpdate',
            'article.trash' => 'onArticleDelete',
            'article.unpublish' => 'onArticleDelete',
            'article.delete' => 'onArticleDelete',

            'user.registered' => 'onUserCreate',
            'user.unlock' => 'onUserCreate',
            'user.lock' => 'onUserDelete',
            'user.update' => 'onUserUpdate',
            'user.change_nickname' => 'onUserUpdate',
            'user.follow' => 'onUserFollow',
            'user.unfollow' => 'onUserUnFollow',

            'classroom.join' => 'onClassroomJoin',
            'classroom.quit' => 'onClassroomQuit',

            'classroom.update' => 'onClassroomUpdate',

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
            'course.thread.elite' => 'onCourseThreadElite',
            'course.thread.unelite' => 'onCourseThreadUnelite',
            'course.thread.stick' => 'onCourseThreadStick',
            'course.thread.unstick' => 'onCourseThreadUnstick',
            'course.thread.post.at' => 'onCourseThreadPostAt',
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
            'course-set.closed' => 'onCourseDelete',

            'open.course.publish' => 'onOpenCourseCreate',
            'open.course.delete' => 'onOpenCourseDelete',
            'open.course.close' => 'onOpenCourseDelete',
            'open.course.update' => 'onOpenCourseUpdate',

            //教学计划购买
            'course.join' => 'onCourseJoin',
            'course.quit' => 'onCourseQuit',

            //兼容模式，task映射到lesson
            'course.task.publish' => 'onCourseLessonCreate',
            'course.task.unpublish' => 'onCourseLessonDelete',
            'course.task.update' => 'onCourseLessonUpdate',
            'course.task.delete' => 'onCourseLessonDelete',

            'coupon.update' => 'onCouponUpdate',

            'exam.reviewed' => 'onExamReviewed',
            'exam.finish' => 'onExamFinish',

            'course.review.add' => 'onCourseReviewAdd',
            'classReview.add' => 'onClassroomReviewAdd',

            'invite.reward' => 'onInviteReward',
            'batch_notification.publish' => 'onBatchNotificationPublish',
        );
    }

    //========= Article Module Start==========

    /**
     * @PushService
     * @SearchService
     */
    public function onArticleCreate(Event $event)
    {
        $article = $event->getSubject();

        if ($this->isIMEnabled()) {
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
                'title' => $article['title'],
                'image' => $article['thumb'],
                'content' => $this->plainText($article['body'], 50), //兼容老字段
                'message' => $this->plainText($article['body'], 50),
            );

            $this->createPushJob($from, $to, $body);
        }

        if ($this->isCloudSearchEnabled()) {
            $args = array(
                'category' => 'article',
            );
            $this->createSearchJob('update', $args);
        }
    }

    /**
     * @param Event $event
     * @SearchService
     */
    public function onArticleUpdate(Event $event)
    {
        $article = $event->getSubject();
        $article = $this->convertArticle($article);

        if ($this->isCloudSearchEnabled()) {
            $args = array(
                'category' => 'article',
            );
            $this->createSearchJob('update', $args);
        }
    }

    public function onArticleDelete(Event $event)
    {
        $article = $event->getSubject();
        $article = $this->convertArticle($article);

        if ($this->isCloudSearchEnabled()) {
            $args = array(
                'category' => 'article',
            );
            $this->createSearchJob('update', $args);
        }
    }

    //========= Article Module End==========

    //======= User Module Start==========
    public function onUserCreate(Event $event)
    {
        $user = $event->getSubject();

        if ($this->isCloudSearchEnabled()) {
            $args = array(
                'category' => 'user',
            );
            $this->createSearchJob('update', $args);
        }
    }

    public function onUserDelete(Event $event)
    {
        $user = $event->getSubject();
        $profile = $this->getUserService()->getUserProfile($user['id']);
        $user = $this->convertUser($user, $profile);

        if ($this->isCloudSearchEnabled()) {
            $args = array(
                'category' => 'user',
                'id' => $user['id'],
            );
            $this->createSearchJob('delete', $args);
        }
    }

    public function onUserUpdate(Event $event)
    {
        $context = $event->getSubject();
        if ($this->isCloudSearchEnabled()) {
            if (!isset($context['user'])) {
                return;
            }

            $args = array(
                'category' => 'user',
            );
            $this->createSearchJob('update', $args);
        }
    }

    public function onUserFollow(Event $event)
    {
        $friend = $event->getSubject();

        if ($this->isIMEnabled()) {
            $user = $this->getUserService()->getUser($friend['fromId']);
            $followedUser = $this->getUserService()->getUser($friend['toId']);

            $imSetting = $this->getSettingService()->get('app_im', array());
            $convNo = isset($imSetting['convNo']) && !empty($imSetting['convNo']) ? $imSetting['convNo'] : '';

            $from = array(
                'id' => $user['id'],
                'type' => 'user',
            );

            $to = array(
                'type' => 'user',
                'id' => $followedUser['id'],
                'convNo' => $convNo,
            );

            $body = array(
                'type' => 'user.follow',
                'fromId' => $user['id'],
                'toId' => $followedUser['id'],
                'title' => '收到一个用户关注',
                'message' => "{$user['nickname']}已经关注了你！",
            );

            $this->createPushJob($from, $to, $body);
        }
    }

    public function onUserUnFollow(Event $event)
    {
        $friend = $event->getSubject();

        if ($this->isIMEnabled()) {
            $user = $this->getUserService()->getUser($friend['fromId']);
            $unFollowedUser = $this->getUserService()->getUser($friend['toId']);

            $imSetting = $this->getSettingService()->get('app_im', array());
            $convNo = isset($imSetting['convNo']) && !empty($imSetting['convNo']) ? $imSetting['convNo'] : '';

            $from = array(
                'id' => $user['id'],
                'type' => 'user',
            );

            $to = array(
                'type' => 'user',
                'id' => $unFollowedUser['id'],
                'convNo' => $convNo,
            );

            $body = array(
                'type' => 'user.unfollow',
                'fromId' => $user['id'],
                'toId' => $unFollowedUser['id'],
                'title' => '用户取消关注',
                'message' => "{$user['nickname']}对你已经取消了关注！",
            );

            $this->createPushJob($from, $to, $body);
        }
    }

    //======== User Module End =========

    //======== Classroom Module Start ========

    public function onClassroomJoin(Event $event)
    {
        $classroom = $event->getSubject();
        $userId = $event->getArgument('userId');

        if ($this->isIMEnabled()) {
            $currentUser = $this->getBiz()->offsetGet('user');
            if (empty($currentUser['id']) || $currentUser['id'] == $userId) {
                return;
            }

            $member = $event->getArgument('member');
            $member['classroom'] = $this->convertClassroom($classroom);
            $member['user'] = $this->convertUser($this->getUserService()->getUser($userId));

            $from = array(
                'type' => 'classroom',
                'id' => $classroom['id'],
            );

            $to = array(
                'type' => 'user',
                'id' => $userId,
                'convNo' => $this->getConvNo(),
            );

            $body = array(
                'type' => 'classroom.join',
                'classroomId' => $classroom['id'],
                'title' => "《{$classroom['title']}》",
                'message' => "您被{$currentUser['nickname']}添加到班级《{$classroom['title']}》",
            );

            $this->createPushJob($from, $to, $body);
        }
    }

    public function onClassroomQuit(Event $event)
    {
        $classroom = $event->getSubject();
        $userId = $event->getArgument('userId');

        if ($this->isIMEnabled()) {
            $currentUser = $this->getBiz()->offsetGet('user');
            if (empty($currentUser) || $currentUser['id'] == $userId) {
                return;
            }

            $member = $event->getArgument('member');
            $member['classroom'] = $this->convertClassroom($classroom);
            $member['user'] = $this->convertUser($this->getUserService()->getUser($userId));

            $from = array(
                'type' => 'classroom',
                'id' => $classroom['id'],
            );

            $to = array(
                'type' => 'user',
                'id' => $userId,
                'convNo' => $this->getConvNo(),
            );

            $body = array(
                'type' => 'classroom.quit',
                'classroomId' => $classroom['id'],
                'title' => "《{$classroom['title']}》",
                'message' => "您被{$currentUser['nickname']}移出班级《{$classroom['title']}》",
            );

            $this->createPushJob($from, $to, $body);
        }
    }

    //========= Classroom Module End ===========

    public function onCourseThreadPostAt(Event $event)
    {
        $threadPost = $event->getSubject();
        $threadPost = $this->convertThreadPost($threadPost, 'course.thread.post.at');

        $currentUser = $this->getUserService()->getUser($threadPost['userId']);

        $users = $event->getArgument('users');

        if ($this->isIMEnabled()) {
            if ('course' != $threadPost['target']['type']) {
                return;
            }

            if (empty($users)) {
                return;
            }

            foreach ($users as $user) {
                $from = array(
                    'type' => $threadPost['target']['type'],
                    'id' => $threadPost['target']['id'],
                );

                $to = array(
                    'type' => 'user',
                    'id' => $user['id'],
                    'convNo' => empty($threadPost['target']['convNo']) ? '' : $threadPost['target']['convNo'],
                );

                $body = array(
                    'type' => 'course.thread.post.at',
                    'threadId' => $threadPost['threadId'],
                    'threadType' => $threadPost['thread']['type'],
                    'courseId' => $threadPost['target']['id'],
                    'lessonId' => $threadPost['thread']['relationId'],
                    'questionCreatedTime' => $threadPost['thread']['createdTime'],
                    'questionTitle' => $threadPost['thread']['title'],
                    'postContent' => $threadPost['content'],
                    'title' => "《{$threadPost['thread']['title']}》",
                    'message' => "{$currentUser['nickname']}《{$threadPost['thread']['title']}》回复中@了你",
                );

                $this->createPushJob($from, $to, $body);
            }
        }
    }

    public function onCourseThreadStick(Event $event)
    {
        $thread = $event->getSubject();
        $thread = $this->convertThread($thread, 'course.thread.stick');
        $user = $this->getBiz()->offsetGet('user');

        if ($this->isIMEnabled()) {
            if (!$user->isAdmin()) {
                return;
            }

            $from = array(
                'type' => $thread['target']['type'],
                'id' => $thread['target']['id'],
            );

            $to = array(
                'type' => 'user',
                'id' => $thread['userId'],
                'convNo' => $this->getConvNo(),
            );
            $threadType = $this->getThreadType($thread['type']);

            $body = array(
                'type' => 'course.thread.stick',
                'courseId' => $thread['target']['id'],
                'threadId' => $thread['id'],
                'threadType' => $thread['type'],
                'title' => "《{$thread['title']}》",
                'message' => "您的{$threadType}《{$thread['title']}》被管理员置顶",
            );

            $this->createPushJob($from, $to, $body);
        }
    }

    public function onCourseThreadUnstick(Event $event)
    {
        $thread = $event->getSubject();
        $thread = $this->convertThread($thread, 'course.thread.unstick');
        $user = $this->getBiz()->offsetGet('user');

        if ($this->isIMEnabled()) {
            if (!$user->isAdmin()) {
                return;
            }

            $from = array(
                'type' => $thread['target']['type'],
                'id' => $thread['target']['id'],
            );

            $to = array(
                'type' => 'user',
                'id' => $thread['userId'],
                'convNo' => $this->getConvNo(),
            );

            $threadType = $this->getThreadType($thread['type']);

            $body = array(
                'type' => 'course.thread.unstick',
                'courseId' => $thread['target']['id'],
                'threadId' => $thread['id'],
                'threadType' => $thread['type'],
                'title' => "《{$thread['title']}》",
                'message' => "您的{$threadType}《{$thread['title']}》被管理员取消置顶",
            );

            $this->createPushJob($from, $to, $body);
        }
    }

    public function onCourseThreadUnelite(Event $event)
    {
        $thread = $event->getSubject();
        $thread = $this->convertThread($thread, 'course.thread.unelite');
        $user = $this->getBiz()->offsetGet('user');

        if ($this->isIMEnabled()) {
            if (!$user->isAdmin()) {
                return;
            }

            $from = array(
                'type' => $thread['target']['type'],
                'id' => $thread['target']['id'],
            );

            $to = array(
                'type' => 'user',
                'id' => $thread['userId'],
                'convNo' => $this->getConvNo(),
            );

            $threadType = $this->getThreadType($thread['type']);

            $body = array(
                'type' => 'course.thread.unelite',
                'courseId' => $thread['target']['id'],
                'threadId' => $thread['id'],
                'threadType' => $thread['type'],
                'title' => "《{$thread['title']}》",
                'message' => "您的{$threadType}《{$thread['title']}》被管理员取消加精",
            );

            $this->createPushJob($from, $to, $body);
        }
    }

    public function onCourseThreadElite(Event $event)
    {
        $thread = $event->getSubject();
        $thread = $this->convertThread($thread, 'course.thread.elite');
        $user = $this->getBiz()->offsetGet('user');

        if ($this->isIMEnabled()) {
            if (!$user->isAdmin()) {
                return;
            }

            $from = array(
                'type' => $thread['target']['type'],
                'id' => $thread['target']['id'],
            );

            $to = array(
                'type' => 'user',
                'id' => $thread['userId'],
                'convNo' => $this->getConvNo(),
            );

            $threadType = $this->getThreadType($thread['type']);

            $body = array(
                'type' => 'course.thread.elite',
                'courseId' => $thread['target']['id'],
                'threadId' => $thread['id'],
                'threadType' => $thread['type'],
                'title' => "《{$thread['title']}》",
                'message' => "您的{$threadType}《{$thread['title']}》被管理员加精",
            );

            $this->createPushJob($from, $to, $body);
        }
    }

    public function onInviteReward(Event $event)
    {
        $inviteCoupon = $event->getSubject();
        $message = $event->getArgument('message');

        if ($this->isIMEnabled()) {
            $from = array(
                'type' => 'coupon',
                'id' => $inviteCoupon['id'],
            );

            $to = array(
                'type' => 'user',
                'id' => $inviteCoupon['userId'],
                'convNo' => $this->getConvNo(),
            );

            $body = array(
                'type' => 'invite.reward',
                'userId' => $inviteCoupon['userId'],
                'title' => "{$message['rewardName']}",
                'message' => "恭喜您获得{$message['rewardName']}奖励，{$message['settingName']}元面值抵价优惠券一张，已发至您的账户",
            );

            $this->createPushJob($from, $to, $body);
        }
    }

    public function onCourseReviewAdd(Event $event)
    {
        $review = $event->getSubject();

        if ($this->isIMEnabled()) {
            if (empty($review['parentId'])) {
                return;
            }
            $course = $this->getCourseService()->getCourse($review['courseId']);

            if (empty($course)) {
                return;
            }
            $parentReview = $this->getCourseReviewService()->getReview($review['parentId']);

            if (empty($parentReview)) {
                return;
            }

            $from = array(
                'id' => $review['id'],
                'type' => 'review',
            );

            $to = array(
                'id' => $parentReview['userId'],
                'type' => 'user',
                'convNo' => $this->getConvNo(),
            );

            $body = array(
                'type' => 'course.review_add',
                'courseId' => $course['id'],
                'reviewId' => $review['id'],
                'parentReviewId' => $parentReview['id'],
                'title' => "您在课程{$course['title']}的评价已被回复",
                'message' => $this->plainText($review['content'], 50),
            );

            $this->createPushJob($from, $to, $body);
        }
    }

    public function onClassroomReviewAdd(Event $event)
    {
        $review = $event->getSubject();

        if ($this->isIMEnabled()) {
            if (empty($review['parentId'])) {
                return;
            }
            $classroom = $this->getClassroomService()->getClassroom($review['classroomId']);

            if (empty($classroom)) {
                return;
            }
            $parentReview = $this->getCourseReviewService()->getReview($review['parentId']);

            if (empty($parentReview)) {
                return;
            }

            $from = array(
                'id' => $review['id'],
                'type' => 'review',
            );

            $to = array(
                'id' => $parentReview['userId'],
                'type' => 'user',
                'convNo' => $this->getConvNo(),
            );

            $body = array(
                'type' => 'classroom.review_add',
                'classroomId' => $classroom['id'],
                'reviewId' => $review['id'],
                'parentReviewId' => $parentReview['id'],
                'title' => "您在班级{$classroom['title']}的评价已被回复",
                'message' => $this->plainText($review['content'], 50),
            );

            $this->createPushJob($from, $to, $body);
        }
    }

    /**
     * Announcement相关.
     *
     * @PushService
     */
    public function onAnnouncementCreate(Event $event)
    {
        $announcement = $event->getSubject();

        if ($this->isIMEnabled()) {
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
            $content = $this->plainText(strip_tags($announcement['content']), 50);
            $body = array(
                'id' => $announcement['id'],
                'type' => 'announcement.create',
                'targetType' => 'announcement',
                'targetId' => $announcement['id'],
                'title' => StringToolkit::specialCharsFilter($content),
            );

            $this->createPushJob($from, $to, $body);
        }
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

        if ($this->isCloudSearchEnabled()) {
            $args = array(
                'category' => 'thread',
            );
            $this->createSearchJob('update', $args);
        }
        if ($this->isIMEnabled()) {
            if ('course' != $thread['target']['type'] || 'question' != $thread['type']) {
                return;
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
                'title' => "{$thread['target']['title']}有新问题",
                'message' => $this->plainText($thread['content'], 50),
            );
            foreach ($thread['target']['teacherIds'] as $teacherId) {
                $to['id'] = $teacherId;
                $this->createPushJob($from, $to, $body);
            }
        }
    }

    public function onGroupThreadCreate(Event $event)
    {
        $thread = $event->getSubject();
        $thread = $this->convertThread($thread, 'group.thread.create');

        if ($this->isCloudSearchEnabled()) {
            $args = array(
                'category' => 'thread',
            );
            $this->createSearchJob('update', $args);
        }

        if ($this->isIMEnabled()) {
            if ('course' != $thread['target']['type'] || 'question' != $thread['type']) {
                return;
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
                'title' => "{$thread['target']['title']}有新问题",
                'message' => $this->plainText($thread['content'], 50),
            );
            foreach ($thread['target']['teacherIds'] as $teacherId) {
                $to['id'] = $teacherId;
                $this->createPushJob($from, $to, $body);
            }
        }
    }

    public function onCourseThreadCreate(Event $event)
    {
        $thread = $event->getSubject();
        $thread = $this->convertThread($thread, 'course.thread.create');

        if ($this->isCloudSearchEnabled()) {
            $args = array(
                'category' => 'thread',
            );
            $this->createSearchJob('update', $args);
        }

        if ('course' != $thread['target']['type'] || 'question' != $thread['type']) {
            return;
        }

        $questionType = ServiceKernel::instance()->trans('course.thread.question_type.'.$thread['questionType']);
        if ($this->isIMEnabled()) {
            if ('course' != $thread['target']['type'] || 'question' != $thread['type']) {
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

            $questionType = ServiceKernel::instance()->trans('course.thread.question_type.'.$thread['questionType']);

            $body = array(
                'type' => 'question.created',
                'threadId' => $thread['id'],
                'threadType' => 'question',
                'courseId' => $thread['target']['id'],
                'lessonId' => $thread['relationId'],
                'questionCreatedTime' => $thread['createdTime'],
                'questionTitle' => $thread['title'],
                'title' => '课程提问',
                'message' => !empty($thread['title']) ? "您的课程有新的提问《{$thread['title']}》" : "有一个{$questionType}类型的提问",
            );

            foreach (array_values($thread['target']['teacherIds']) as $i => $teacherId) {
                if ($i >= 3) {
                    break;
                }
                $to['id'] = $teacherId;

                $this->createPushJob($from, $to, $body);
            }
        }
        //推送
//        if (!empty($thread['target']['teacherIds'])) {
//            $devices = $this->getPushDeviceService()->findPushDeviceByUserIds($thread['target']['teacherIds']);
//            $reg_ids = ArrayToolkit::column($devices, 'regId');
//            if (!empty($reg_ids)) {
//                $message = array(
//                    'reg_ids' => implode(',', $reg_ids),
//                    'pass_through_type' => 'normal',
//                    'payload' => json_encode(array('courseId' => $thread['target']['id'], 'threadId' => $thread['id'], 'type' => 'course.thread.create')),
//                    'title' => '课程提问',
//                    'description' => !empty($thread['content']) ? $this->plainText(strip_tags($thread['content']), 10) : "有一个{$questionType}类型的提问",
//                );
//                $result = $this->getPushDeviceService()->getPushSdk()->pushMessage($message);
//                $this->getLogService()->info(
//                    'push',
//                    'course_thread_create',
//                    '创建问题-推送消息',
//                    array('result' => $result, 'message' => $message)
//                );
//            }
//        }
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
        if ($this->isIMEnabled()) {
            if ('course' != $threadPost['target']['type'] || empty($threadPost['target']['teacherIds'])) {
                return;
            }

            if ('question' != $threadPost['thread']['type']) {
                return;
            }

            foreach ($threadPost['target']['teacherIds'] as $teacherId) {
                if ($teacherId != $threadPost['userId']) {
                    continue;
                }

                $from = array(
                    'type' => $threadPost['target']['type'],
                    'id' => $threadPost['target']['id'],
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
                    'title' => "{$threadPost['thread']['title']}有新回复",
                    'message' => $this->plainText($threadPost['content'], 50),
                );

                $this->createPushJob($from, $to, $body);
            }
        }
    }

    public function onCourseThreadPostCreate(Event $event)
    {
        $threadPost = $event->getSubject();
        $threadPost = $this->convertThreadPost($threadPost, 'course.thread.post.create');

        $user = $this->getBiz()->offsetGet('user');
        if ($threadPost['thread']['userId'] == $user['id'] && 'question' != $threadPost['thread']['type']) {
            return;
        }
        $postUser = $this->getUserService()->getUser($threadPost['userId']);
        $threadType = $this->getThreadType($threadPost['thread']['type']);
        $threadPostType = !empty($threadPost['postType']) ? ServiceKernel::instance()->trans('course.thread.question_type.'.$threadPost['postType']) : '';

        //学生追问，老师收到推送
        if ($threadPost['thread']['userId'] == $user['id'] && 'question' == $threadPost['thread']['type']) {
            //推送
//            if (!empty($threadPost['target']['teacherIds'])) {
//                $devices = $this->getPushDeviceService()->findPushDeviceByUserIds($threadPost['target']['teacherIds']);
//                $reg_ids = ArrayToolkit::column($devices, 'regId');
//                if (!empty($reg_ids)) {
//                    $message = array(
//                        'reg_ids' => implode(',', $reg_ids),
//                        'pass_through_type' => 'normal',
//                        'payload' => json_encode(array('courseId' => $threadPost['target']['id'], 'threadId' => $threadPost['threadId'], 'type' => 'course.thread.create')),
//                        'title' => '课程追问',
//                        'description' => !empty($threadPost['content']) ? $this->plainText(strip_tags($threadPost['content']), 10) : "有一个{$threadPostType}类型的追问",
//                    );
//                    $result = $this->getPushDeviceService()->getPushSdk()->pushMessage($message);
//                    $this->getLogService()->info(
//                        'push',
//                        'course_thread_create',
//                        '课程追问-推送消息',
//                        array('result' => $result, 'message' => $message)
//                    );
//                }
//            }

            if ($this->isIMEnabled()) {
                $body = array(
                    'type' => 'question.answered',
                    'threadId' => $threadPost['threadId'],
                    'threadType' => $threadPost['thread']['type'],
                    'courseId' => $threadPost['target']['id'],
                    'lessonId' => $threadPost['thread']['relationId'],
                    'questionCreatedTime' => $threadPost['thread']['createdTime'],
                    'questionTitle' => $threadPost['thread']['title'],
                    'postContent' => $threadPost['content'],
                    'title' => "{$threadPostType}追问",
                    'message' => !empty($threadPost['content']) ? $this->plainText(strip_tags($threadPost['content']), 10) : "你有一个{$threadPostType}的追问",
                );

                $from = array(
                    'type' => $threadPost['target']['type'],
                    'id' => $threadPost['target']['id'],
                );

                $to = array(
                    'type' => 'user',
                    'convNo' => empty($threadPost['target']['convNo']) ? '' : $threadPost['target']['convNo'],
                );

                foreach (array_values($threadPost['target']['teacherIds']) as $i => $teacherId) {
                    if ($i >= 3) {
                        break;
                    }
                    $to['id'] = $teacherId;

                    $this->createPushJob($from, $to, $body);
                }
            }

            return;
        }

        //回复收到
        $body = array(
            'type' => 'question.answered',
            'threadId' => $threadPost['threadId'],
            'threadType' => $threadPost['thread']['type'],
            'courseId' => $threadPost['target']['id'],
            'lessonId' => $threadPost['thread']['relationId'],
            'questionCreatedTime' => $threadPost['thread']['createdTime'],
            'questionTitle' => $threadPost['thread']['title'],
            'postContent' => $threadPost['content'],
            'title' => "{$threadType}回答",
            'message' => !empty($threadPost['thread']['title']) ? "[{$postUser['nickname']}]回复了你的{$threadType}《{$threadPost['thread']['title']}》" : "[{$postUser['nickname']}]回复了你的{$threadPostType}{$threadType}",
        );

        //推送
//        if ($threadPost['thread']['type'] == 'question') {
//            $devices = $this->getPushDeviceService()->getPushDeviceByUserId($threadPost['thread']['userId']);
//            if (!empty($devices['regId'])) {
//                $message = array(
//                    'reg_ids' => $devices['regId'],
//                    'pass_through_type' => 'normal',
//                    'payload' => json_encode(array('courseId' => $threadPost['target']['id'], 'threadId' => $threadPost['threadId'], 'postId' => $threadPost['id'], 'type' => 'course.thread.post.create')),
//                    'title' => '课程回答',
//                    'description' => !empty($threadPost['content']) ? $this->plainText(strip_tags($threadPost['content']), 10) : "有一个{$threadPostType}类型的回答",
//                );
//                $result = $this->getPushDeviceService()->getPushSdk()->pushMessage($message);
//                $this->getLogService()->info(
//                    'push',
//                    'course_thread_post_create',
//                    '老师回答-推送消息',
//                    array('result' => $result, 'message' => $message)
//                );
//            }
//        }

        if ($this->isIMEnabled()) {
            $from = array(
                'type' => $threadPost['target']['type'],
                'id' => $threadPost['target']['id'],
            );

            $to = array(
                'type' => 'user',
                'id' => $threadPost['thread']['userId'],
                'convNo' => empty($threadPost['target']['convNo']) ? '' : $threadPost['target']['convNo'],
            );

            $this->createPushJob($from, $to, $body);
        }
    }

    public function onGroupThreadPostCreate(Event $event)
    {
        $post = $event->getSubject();
        $post = $this->convertThreadPost($post, 'group.thread.post.create');

        if ($this->isIMEnabled()) {
            if ('course' != $post['target']['type'] || empty($post['target']['teacherIds'])) {
                return;
            }

            if ('question' != $post['thread']['type']) {
                return;
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
                    'message' => $this->plainText($post['content'], 50),
                );

                $this->createPushJob($from, $to, $body);
            }
        }
    }

    public function onCourseJoin(Event $event)
    {
        $course = $event->getSubject();
        $userId = $event->getArgument('userId');
        $member = $event->getArgument('member');

        if ($this->isIMEnabled()) {
            $currentUser = $this->getBiz()->offsetGet('user');

            if (!empty($course['parentId'])) {
                return;
            }

            if ($currentUser['id'] == $member['userId'] || empty($currentUser['id'])) {
                return;
            }

            $course = $this->convertCourse($course);
            $member['course'] = $course;
            $member['user'] = $this->convertUser($this->getUserService()->getUser($userId));

            $imSetting = $this->getSettingService()->get('app_im', array());
            $member['convNo'] = isset($imSetting['convNo']) && !empty($imSetting['convNo']) ? $imSetting['convNo'] : '';

            $from = array(
                'type' => 'course',
                'id' => $course['id'],
            );

            $to = array(
                'type' => 'user',
                'id' => $member['userId'],
                'convNo' => $member['convNo'],
            );

            $body = array(
                'type' => 'course.join',
                'courseId' => $course['id'],
                'courseTitle' => $course['title'],
                'teacherId' => $userId,
                'teacherName' => $member['user']['id'],
                'title' => "《{$course['title']}》",
                'message' => "您被{$currentUser['nickname']}添加到《{$course['title']}》",
            );

            $this->createPushJob($from, $to, $body);
        }
    }

    public function onCourseQuit(Event $event)
    {
        $course = $event->getSubject();
        $userId = $event->getArgument('userId');
        $member = $event->getArgument('member');

        if ($this->isIMEnabled()) {
            $currentUser = $this->getBiz()->offsetGet('user');

            if (!empty($course['parentId'])) {
                return;
            }

            if ($currentUser['id'] == $member['userId']) {
                return;
            }

            $course = $this->convertCourse($course);
            $member['course'] = $course;
            $member['user'] = $this->convertUser($this->getUserService()->getUser($userId));

            $imSetting = $this->getSettingService()->get('app_im', array());
            $member['convNo'] = isset($imSetting['convNo']) && !empty($imSetting['convNo']) ? $imSetting['convNo'] : '';

            $from = array(
                'type' => 'course',
                'id' => $course['id'],
            );

            $to = array(
                'type' => 'user',
                'id' => $member['userId'],
                'convNo' => $member['convNo'],
            );

            $body = array(
                'type' => 'course.quit',
                'courseId' => $course['id'],
                'courseTitle' => $course['title'],
                'teacherId' => $userId,
                'teacherName' => $member['user']['id'],
                'title' => "《{$course['title']}》",
                'message' => "您被{$currentUser['nickname']}移出《{$course['title']}》",
            );

            $this->createPushJob($from, $to, $body);
        }
    }

    public function onExamReviewed(Event $event)
    {
        $testpaperResult = $event->getSubject();

        if ($this->isIMEnabled()) {
            $teacher = $this->getUserService()->getUser($testpaperResult['checkTeacherId']);

            $testType = '';
            if ('testpaper' == $testpaperResult['type']) {
                $testType = '试卷';
            } elseif ('homework' == $testpaperResult['type']) {
                $testType = '作业';
            }

            $from = array(
                'type' => 'testpaper',
                'id' => $testpaperResult['testId'],
            );

            $to = array(
                'type' => 'user',
                'id' => $testpaperResult['userId'],
                'convNo' => $this->getConvNo(),
            );

            $body = array(
                'type' => 'testpaper.reviewed',
                'testpaperResultId' => $testpaperResult['id'],
                'testpaperResultName' => $testpaperResult['paperName'],
                'testId' => $testpaperResult['testId'],
                'title' => "《{$testpaperResult['paperName']}》",
                'message' => "{$teacher['nickname']}批阅了你的{$testType}《{$testpaperResult['paperName']}》,快去查看吧！",
            );

            $this->createPushJob($from, $to, $body);
        }
    }

    public function onExamFinish(Event $event)
    {
        $testpaperResult = $event->getSubject();

        if ($this->isIMEnabled()) {
            $course = $this->getCourseService()->getCourse($testpaperResult['courseId']);

            $user = $this->getUserService()->getUser($testpaperResult['userId']);

            $imSetting = $this->getSettingService()->get('app_im', array());
            $convNo = isset($imSetting['convNo']) && !empty($imSetting['convNo']) ? $imSetting['convNo'] : '';

            $testType = '';
            if ('testpaper' == $testpaperResult['type']) {
                $testType = '试卷';
            } elseif ('homework' == $testpaperResult['type']) {
                $testType = '作业';
            }

            $from = array(
                'type' => 'testpaper',
                'id' => $testpaperResult['testId'],
            );

            $to = array(
                'type' => 'user',
                'convNo' => $convNo,
            );

            $body = array(
                'type' => 'testpaper.finished',
                'testpaperResultId' => $testpaperResult['id'],
                'testpaperResultName' => $testpaperResult['paperName'],
                'testId' => $testpaperResult['testId'],
                'title' => "《{$testpaperResult['paperName']}》",
                'message' => "{$user['nickname']}刚刚完成了{$testType}《{$testpaperResult['paperName']}》,快去查看吧！",
            );

            if (empty($course['teacherIds'])) {
                return;
            }

            foreach ($course['teacherIds'] as $teacherId) {
                $to['id'] = $teacherId;

                $this->createPushJob($from, $to, $body);
            }
        }
    }

    protected function pushCloud($eventName, array $data, $level = 'normal')
    {
        return $this->getCloudDataService()->push('school.'.$eventName, $data, time(), $level);
    }

    public function onCouponUpdate(Event $event)
    {
        $coupon = $event->getSubject();
        if ('receive' != $coupon['status']) {
            return;
        }
        if ($this->isIMEnabled()) {
            $from = array(
                'type' => 'coupon',
                'id' => $coupon['id'],
            );

            $to = array(
                'type' => 'user',
                'id' => $coupon['userId'],
                'convNo' => $this->getConvNo(),
            );

            if ('minus' == $coupon['type']) {
                $message = '您有一张价值'.$coupon['rate'].'元的优惠券领取成功';
            } else {
                $message = '您有一张抵扣为'.$coupon['rate'].'折的优惠券领取成功';
            }

            $body = array(
                'type' => 'coupon.receive',
                'couponId' => $coupon['id'],
                'title' => '获得新的优惠券',
                'message' => $message,
            );

            $this->createPushJob($from, $to, $body);
        }
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

        if ($this->isCloudSearchEnabled()) {
            $args = array(
                'category' => 'course',
            );
            $this->createSearchJob('update', $args);
        }
    }

    public function onCourseCreate(Event $event)
    {
        $course = $event->getSubject();

        if ($this->isCloudSearchEnabled()) {
            $args = array(
                'category' => 'course',
            );
            $this->createSearchJob('update', $args);
        }
    }

    public function onCourseUpdate(Event $event)
    {
        $course = $event->getSubject();

        if ($this->isCloudSearchEnabled()) {
            $args = array(
                'category' => 'course',
            );
            $this->createSearchJob('update', $args);
        }
    }

    public function onCourseDelete(Event $event)
    {
        $course = $event->getSubject();
        $course = $this->convertCourse($course);

        if ($this->isCloudSearchEnabled()) {
            $args = array(
                'category' => 'course',
                'id' => $course['id'],
            );
            $this->createSearchJob('delete', $args);
        }
    }

    protected function convertCourse($course)
    {
        $course['smallPicture'] = isset($course['cover']['small']) ? $this->getFileUrl($course['cover']['small']) : '';
        $course['middlePicture'] = isset($course['cover']['middle']) ? $this->getFileUrl($course['cover']['middle']) : '';
        $course['largePicture'] = isset($course['cover']['large']) ? $this->getFileUrl($course['cover']['large']) : '';
        $course['about'] = isset($course['summary']) ? $this->convertHtml($course['summary']) : '';

        $course['title'] = CourseTitleUtils::getDisplayedTitle($course);

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

        if ((!isset($mobileSetting['enable']) || $mobileSetting['enable']) && 'live' == $lesson['type']) {
            $this->createJob($lesson);
        }
        if ($this->isCloudSearchEnabled()) {
            $args = array(
                'category' => 'lesson',
            );
            $this->createSearchJob('update', $args);
        }
    }

    public function onCourseLessonUpdate(Event $event)
    {
        $lesson = $event->getSubject();
        $oldTask = $event->getArguments();
        $mobileSetting = $this->getSettingService()->get('mobile');

        $shouldReCreatePushJOB = 'live' == $lesson['type'] && isset($oldTask['startTime']) && $oldTask['startTime'] != $lesson['startTime'] && (!isset($mobileSetting['enable']) || $mobileSetting['enable']);
        if ($shouldReCreatePushJOB) {
            $this->deleteJob($lesson);

            if ('published' == $lesson['status']) {
                //这个任务要关注，得改
                $this->createJob($lesson);
            }
        }

        if ($this->isCloudSearchEnabled()) {
            $args = array(
                'category' => 'lesson',
            );
            $this->createSearchJob('update', $args);
        }
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

        if ($this->isCloudSearchEnabled()) {
            $args = array(
                'category' => 'lesson',
                'id' => $lesson['id'],
            );
            $this->createSearchJob('delete', $args);
        }
    }

    protected function convertClassroom($classroom)
    {
        $classroom['smallPicture'] = $this->getFileUrl($classroom['smallPicture']);
        $classroom['middlePicture'] = $this->getFileUrl($classroom['middlePicture']);
        $classroom['largePicture'] = $this->getFileUrl($classroom['largePicture']);
        $classroom['about'] = $this->convertHtml($classroom['about']);

        return $classroom;
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

        if ($this->isCloudSearchEnabled()) {
            $args = array(
                'category' => 'thread',
            );
            $this->createSearchJob('update', $args);
        }
    }

    /**
     * @param Event $event
     * @SearchService
     */
    public function onThreadUpdate(Event $event)
    {
        $thread = $event->getSubject();
        $thread = $this->convertThread($thread, 'thread.update');

        if ($this->isCloudSearchEnabled()) {
            $args = array(
                'category' => 'thread',
            );
            $this->createSearchJob('update', $args);
        }
    }

    public function onCourseThreadUpdate(Event $event)
    {
        $thread = $event->getSubject();
        $thread = $this->convertThread($thread, 'course.thread.update');

        $user = $this->getBiz()->offsetGet('user');

        if ($this->isIMEnabled()) {
            //            if (!$user->isAdmin() || $user['id'] == $thread['userId']) {
            //                return;
            //            }
            if ($user['id'] == $thread['userId']) {
                return;
            }

            $from = array(
                'type' => $thread['target']['type'],
                'id' => $thread['target']['id'],
            );

            $to = array(
                'type' => 'user',
                'id' => $thread['userId'],
                'convNo' => $this->getConvNo(),
            );

            $threadType = $this->getThreadType($thread['type']);
            $questionType = ServiceKernel::instance()->trans('course.thread.question_type.'.$thread['questionType']);

            $body = array(
                'type' => 'course.thread.update',
                'courseId' => $thread['target']['id'],
                'threadId' => $thread['id'],
                'threadType' => $thread['type'],
                'title' => "《{$thread['title']}》",
                'message' => (!empty($thread['title'])) ? "您的{$threadType}《{$thread['title']}》被[{$user['nickname']}]编辑" : "您的{$questionType}{$threadType}被[{$user['nickname']}]编辑",
            );

            $this->createPushJob($from, $to, $body);
        }

        if ($this->isCloudSearchEnabled()) {
            $args = array(
                'category' => 'thread',
            );
            $this->createSearchJob('update', $args);
        }
    }

    public function onGroupThreadUpdate(Event $event)
    {
        $thread = $event->getSubject();
        $thread = $this->convertThread($thread, 'group.thread.update');

        if ($this->isCloudSearchEnabled()) {
            $args = array(
                'category' => 'thread',
            );
            $this->createSearchJob('update', $args);
        }
    }

    public function onThreadDelete(Event $event)
    {
        $thread = $event->getSubject();
        $thread = $this->convertThread($thread, 'thread.delete');

        if ($this->isCloudSearchEnabled()) {
            $args = array(
                'category' => 'thread',
                'id' => $thread['targetType'].'_'.$thread['id'],
            );
            $this->createSearchJob('delete', $args);
        }
    }

    public function onCourseThreadDelete(Event $event)
    {
        $thread = $event->getSubject();
        $thread = $this->convertThread($thread, 'course.thread.delete');

        if ($this->isIMEnabled()) {
            $user = $this->getBiz()->offsetGet('user');
            $from = array(
                'type' => $thread['target']['type'],
                'id' => $thread['target']['id'],
            );

            $to = array(
                'type' => 'user',
                'id' => $thread['userId'],
                'convNo' => $this->getConvNo(),
            );

            $threadType = $this->getThreadType($thread['type']);
            $questionType = ServiceKernel::instance()->trans('course.thread.question_type.'.$thread['questionType']);

            $body = array(
                'type' => 'course.thread.delete',
                'courseId' => $thread['target']['id'],
                'threadId' => $thread['id'],
                'threadType' => $thread['type'],
                'title' => "《{$thread['title']}》",
                'message' => !empty($thread['title']) ? "您的{$threadType}《{$thread['title']}》被[{$user['nickname']}]删除" : "您的{$questionType}{$threadType}被[{$user['nickname']}]删除",
            );

            $this->createPushJob($from, $to, $body);
        }

        if ($this->isCloudSearchEnabled()) {
            $args = array(
                'category' => 'thread',
                'id' => $thread['target']['type'].'_'.$thread['id'],
            );
            $this->createSearchJob('delete', $args);
        }
    }

    public function onGroupThreadDelete(Event $event)
    {
        $thread = $event->getSubject();
        $thread = $this->convertThread($thread, 'group.thread.delete');

        if ($this->isCloudSearchEnabled()) {
            $args = array(
                'category' => 'thread',
                'id' => $thread['target']['type'].'_'.$thread['id'],
            );
            $this->createSearchJob('delete', $args);
        }
    }

    public function onGroupThreadClose(Event $event)
    {
        $thread = $event->getSubject();
        $thread = $this->convertThread($thread, 'group.thread.close');

        if ($this->isCloudSearchEnabled()) {
            $args = array(
                'category' => 'thread',
                'id' => $thread['target']['type'].'_'.$thread['id'],
            );
            $this->createSearchJob('delete', $args);
        }
    }

    protected function convertThread($thread, $eventName)
    {
        if (0 === strpos($eventName, 'course')) {
            $thread['targetType'] = 'course';
            $thread['targetId'] = $thread['courseId'];
            $thread['relationId'] = $thread['taskId'];
        } elseif (0 === strpos($eventName, 'group')) {
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
        $converted['targetType'] = empty($thread['targetType']) ? '' : $thread['targetType'];
        $converted['questionType'] = empty($thread['questionType']) ? '' : $thread['questionType'];

        return $converted;
    }

    public function onCourseThreadPostUpdate(Event $event)
    {
        $threadPost = $event->getSubject();
        $threadPost = $this->convertThreadPost($threadPost, 'course.thread.post.update');
        $user = $this->getBiz()->offsetGet('user');

        if ($this->isIMEnabled()) {
            if ('course' != $threadPost['target']['type']) {
                return;
            }
            //
            //            if ($threadPost['thread']['type'] != 'question') {
            //                return;
            //            }

            //            if (!$user->isAdmin()) {
            //                return;
            //            }

            $from = array(
                'type' => $threadPost['target']['type'],
                'id' => $threadPost['target']['id'],
            );

            $to = array(
                'type' => 'user',
                'id' => $threadPost['thread']['userId'],
                'convNo' => empty($threadPost['target']['convNo']) ? '' : $threadPost['target']['convNo'],
            );

            $threadType = $this->getThreadType($threadPost['thread']['type']);
            $threadPostType = !empty($threadPost['postType']) ? ServiceKernel::instance()->trans('course.thread.question_type.'.$threadPost['postType']) : '';

            $body = array(
                'type' => 'course.thread.post.update',
                'threadId' => $threadPost['threadId'],
                'threadType' => $threadPost['thread']['type'],
                'courseId' => $threadPost['target']['id'],
                'lessonId' => $threadPost['thread']['relationId'],
                'questionCreatedTime' => $threadPost['thread']['createdTime'],
                'questionTitle' => $threadPost['thread']['title'],
                'postContent' => $threadPost['content'],
                'title' => "《{$threadPost['thread']['title']}》",
                'message' => !empty($threadPost['thread']['title']) ? "您的{$threadType}《{$threadPost['thread']['title']}》有回复被[{$user['nickname']}]编辑" : "您的{$threadPostType}{$threadType}有回复被[{$user['nickname']}]编辑",
            );

            $this->createPushJob($from, $to, $body);
        }
    }

    public function onThreadPostDelete(Event $event)
    {
        $threadPost = $event->getSubject();
        $this->pushCloud('thread_post.delete', $this->convertThreadPost($threadPost, 'thread.post.delete'));
    }

    public function onCourseThreadPostDelete(Event $event)
    {
        $threadPost = $event->getSubject();
        $threadPost = $this->convertThreadPost($threadPost, 'course.thread.post.delete');

        if ($this->isIMEnabled()) {
            //            if ($threadPost['target']['type'] != 'course' || empty($threadPost['target']['teacherIds'])) {
            //                return;
            //            }
            if ('course' != $threadPost['target']['type']) {
                return;
            }
            //
            //            if ($threadPost['thread']['type'] != 'question') {
            //                return;
            //            }
            $user = $this->getBiz()->offsetGet('user');

            $from = array(
                'type' => $threadPost['target']['type'],
                'id' => $threadPost['target']['id'],
            );

            $to = array(
                'type' => 'user',
                'id' => $threadPost['thread']['userId'],
                'convNo' => empty($threadPost['target']['convNo']) ? '' : $threadPost['target']['convNo'],
            );

            $threadType = $this->getThreadType($threadPost['thread']['type']);
            $threadPostType = !empty($threadPost['postType']) ? ServiceKernel::instance()->trans('course.thread.question_type.'.$threadPost['postType']) : '';

            $body = array(
                'type' => 'course.thread.post.delete',
                'threadId' => $threadPost['threadId'],
                'threadType' => $threadPost['thread']['type'],
                'courseId' => $threadPost['target']['id'],
                'lessonId' => $threadPost['thread']['relationId'],
                'questionCreatedTime' => $threadPost['thread']['createdTime'],
                'questionTitle' => $threadPost['thread']['title'],
                'postContent' => $threadPost['content'],
                'title' => "《{$threadPost['thread']['title']}》",
                'message' => !empty($threadPost['thread']['title']) ? "您的{$threadType}《{$threadPost['thread']['title']}》有回复被[{$user['nickname']}]删除" : "您的{$threadPostType}{$threadType}有回复被[{$user['nickname']}]删除",
            );

            $this->createPushJob($from, $to, $body);
        }
    }

    public function onGroupThreadPostDelete(Event $event)
    {
        $threadPost = $event->getSubject();
        $this->pushCloud('thread_post.delete', $this->convertThreadPost($threadPost, 'group.thread.post.delete'));
    }

    protected function convertThreadPost($threadPost, $eventName)
    {
        if (0 === strpos($eventName, 'course')) {
            $threadPost['targetType'] = 'course';
            $threadPost['targetId'] = $threadPost['courseId'];
            $threadPost['thread'] = $this->convertThread(
                $this->getThreadService('course')->getThread($threadPost['courseId'], $threadPost['threadId']),
                $eventName
            );
        } elseif (0 === strpos($eventName, 'group')) {
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
        $converted['postType'] = isset($threadPost['postType']) ? $threadPost['postType'] : '';

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

        if ($this->isCloudSearchEnabled()) {
            $args = array(
                'category' => 'openCourse',
            );
            $this->createSearchJob('update', $args);
        }
    }

    public function onOpenCourseDelete(Event $event)
    {
        $openCourse = $event->getSubject();
        $openCourse = $this->convertOpenCourse($openCourse);

        if ($this->isCloudSearchEnabled()) {
            $args = array(
                'category' => 'openCourse',
                'id' => $openCourse['id'],
            );
            $this->createSearchJob('delete', $args);
        }
    }

    public function onOpenCourseUpdate(Event $event)
    {
        $subject = $event->getSubject();
        $course = $subject['course'];
        $course = $this->convertOpenCourse($course);

        if ($this->isCloudSearchEnabled() && 'published' == $course['status']) {
            $args = array(
                'category' => 'openCourse',
            );
            $this->createSearchJob('update', $args);
        }
    }

    public function onBatchNotificationPublish(Event $event)
    {
        $batchNotification = $event->getSubject();
        if ($this->isIMEnabled()) {
            $from = array(
                'type' => 'batch_notification',
                'id' => $batchNotification['id'],
            );

            $to = array(
                'type' => 'global',
                'convNo' => $this->getConvNo(),
            );
            $content = $this->plainText(strip_tags($batchNotification['content']), 50);
            $body = array(
                'type' => 'batch_notification.publish',
                'targetType' => 'batch_notification',
                'targetId' => $batchNotification['id'],
                'title' => StringToolkit::specialCharsFilter($batchNotification['title']),
                'message' => StringToolkit::specialCharsFilter($content),
                'source' => 'notification',
            );

            $this->createPushJob($from, $to, $body);
        }
    }

    //-----------班级相关----------

    public function onClassroomUpdate(Event $event)
    {
        $args = $event->getSubject();
        $classroom = empty($args['classroom']) ? null : $args['classroom'];
        $fields = empty($args['fields']) ? null : $args['fields'];

        if (!empty($fields)) {
            if ($this->isCloudSearchEnabled()) {
                if ('draft' == $classroom['status']) {
                    return;
                }
                if ('published' == $classroom['status']) {
                    $args = array(
                        'category' => 'classroom',
                    );
                    $this->createSearchJob('update', $args);
                }

                if ('closed' == $classroom['status']) {
                    $args = array(
                        'category' => 'classroom',
                        'id' => $classroom['id'],
                    );
                    $this->createSearchJob('delete', $args);
                }
            }
        }
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
        //        if ($lesson['startTime'] >= (time() + 60 * 60)) {
        //            $startJob = array(
        //                'name' => 'PushNotificationOneHourJob_lesson_'.$lesson['id'],
        //                'expression' => $lesson['startTime'] - 60 * 60,
        //                'class' => 'Biz\Notification\Job\PushNotificationOneHourJob',
        //                'args' => array(
        //                    'targetType' => 'lesson',
        //                    'targetId' => $lesson['id'],
        //                ),
        //            );
        //            $this->getSchedulerService()->register($startJob);
        //        }

        //在直播开始前，通知都有效，但不是一直需要执行
        if ('live' == $lesson['type']) {
            $startJob = array(
                'name' => 'LiveCourseStartNotifyJob_liveLesson_'.$lesson['id'],
                'expression' => intval($lesson['startTime'] - 10 * 60),
                'class' => 'Biz\Notification\Job\LiveLessonStartNotifyJob',
                'misfire_threshold' => 10 * 60,
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

    private function isCloudSearchEnabled()
    {
        $setting = $this->getSettingService()->get('cloud_search', array());

        if (empty($setting) || empty($setting['search_enabled'])) {
            return false;
        }

        return true;
    }

    public function isIMEnabled()
    {
        $setting = $this->getSettingService()->get('app_im', array());

        if (empty($setting) || empty($setting['enabled'])) {
            return false;
        }

        return true;
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
        if ('course' == $type) {
            return $this->createService('Course:ThreadService');
        }

        if ('group' == $type) {
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

    protected function getLogService()
    {
        return $this->createService('System:LogService');
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
     * @return ReviewServiceImpl
     */
    protected function getCourseReviewService()
    {
        return $this->createService('Course:ReviewService');
    }

    /**
     * @return ClassroomReviewService
     */
    protected function getClassroomReviewService()
    {
        return $this->createService('Classroom:ClassroomReviewService');
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

    private function getConvNo()
    {
        $imSetting = $this->getSettingService()->get('app_im', array());
        $convNo = isset($imSetting['convNo']) && !empty($imSetting['convNo']) ? $imSetting['convNo'] : '';

        return $convNo;
    }

    private function createPushJob($from, $to, $body)
    {
        $pushJob = new PushJob(array(
            'from' => $from,
            'to' => $to,
            'body' => $body,
        ));

        $this->getQueueService()->pushJob($pushJob);
    }

    private function createSearchJob($type, $args)
    {
        $notifyJob = new SearchJob(array(
            'type' => $type,
            'args' => $args,
        ));

        $this->getQueueService()->pushJob($notifyJob);
    }

    /**
     * @return \Biz\PushDevice\Service\Impl\PushDeviceServiceImpl
     */
    protected function getPushDeviceService()
    {
        return $this->createService('PushDevice:PushDeviceService');
    }

    public function getThreadType($type)
    {
        $types = array(
            'discussion' => '话题',
            'question' => '问答',
            'event' => '活动',
        );

        return empty($types[$type]) ? '' : $types[$type];
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

        if ('user' == $to['type']) {
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
