<?php

namespace Tests\Unit\Notification;

use Biz\BaseTestCase;
use Biz\Course\Service\Impl\CourseSetServiceImpl;
use Codeages\Biz\Framework\Event\Event;
use Biz\Notification\Event\PushMessageEventSubscriber;
use Tests\Unit\Notification\Tool\MockedQueueServiceImpl;

class PushMessageEventSubscriberTest extends BaseTestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->enableIm();
    }

    public function testOnArticleCreateWithCloudSearchOn()
    {
        $this->createArticleTestData(true);

        $this->assertArrayEquals(
            array(
                'type' => 'update',
                'args' => array(
                    'category' => 'article',
                ),
            ),
            $this->getQueueService()->getJob()->getBody()
        );
    }

    public function testOnArticleCreateWithCloudSearchOff()
    {
        $this->createArticleTestData(false);

        $this->assertArrayEquals(
            array(
                'from' => array(
                    'id' => 2,
                    'type' => 'news',
                ),
                'to' => array(
                    'type' => 'global',
                    'convNo' => '',
                ),
                'body' => array(
                    'type' => 'news.create',
                    'id' => 123,
                    'title' => 'article title',
                    'image' => 'http://test.com/files/thumb.png',
                    'content' => 'article title',
                    'message' => 'article title',
                ),
            ),
            $this->getQueueService()->getJob()->getBody()
        );
    }

    public function testOnArticleUpdateWithCloudSearchOn()
    {
        $this->enableCloudSearch();
        $subscriber = $this->getEventSubscriberWithMockedQueue();
        $subscriber->onArticleUpdate($this->getArticleEvent());
        $this->assertArrayEquals(
            array(
                'type' => 'update',
                'args' => array(
                    'category' => 'article',
                ),
            ),
            $this->getQueueService()->getJob()->getBody()
        );
    }

    public function testOnArticleDeleteWithCloudSearchOn()
    {
        $this->enableCloudSearch();
        $subscriber = $this->getEventSubscriberWithMockedQueue();
        $subscriber->onArticleDelete($this->getArticleEvent());
        $this->assertArrayEquals(
            array(
                'type' => 'update',
                'args' => array(
                    'category' => 'article',
                ),
            ),
            $this->getQueueService()->getJob()->getBody()
        );
    }

    public function testOnUserFollow()
    {
        $this->mockUserInfo();
        $subscriber = $this->getEventSubscriberWithMockedQueue();
        $subscriber->onUserFollow($this->getUserEvent());

        $this->assertEquals(
            array(
                'from' => array(
                    'id' => 1,
                    'type' => 'user',
                ),
                'to' => array(
                    'type' => 'user',
                    'id' => 2,
                    'convNo' => '',
                ),
                'body' => array(
                    'type' => 'user.follow',
                    'fromId' => 1,
                    'toId' => 2,
                    'title' => '收到一个用户关注',
                    'message' => '用户1已经关注了你！',
                ),
            ),
            $this->getQueueService()->getJob()->getBody()
        );
    }

    public function testOnUserUnFollow()
    {
        $this->mockUserInfo();
        $subscriber = $this->getEventSubscriberWithMockedQueue();
        $subscriber->onUserUnFollow($this->getUserEvent());

        $this->assertEquals(
            array(
                'from' => array(
                    'id' => 1,
                    'type' => 'user',
                ),
                'to' => array(
                    'type' => 'user',
                    'id' => 2,
                    'convNo' => '',
                ),
                'body' => array(
                    'type' => 'user.unfollow',
                    'fromId' => 1,
                    'toId' => 2,
                    'title' => '用户取消关注',
                    'message' => '用户1对你已经取消了关注！',
                ),
            ),
            $this->getQueueService()->getJob()->getBody()
        );
    }

    public function testOnClassroomJoin()
    {
        $this->mockClassroomUser();
        $subscriber = $this->getEventSubscriberWithMockedQueue();
        $subscriber->onClassroomJoin($this->getClassroomEvent());

        $this->assertArrayEquals(
            array(
                'from' => array(
                    'type' => 'classroom',
                    'id' => '12',
                ),
                'to' => array(
                    'type' => 'user',
                    'id' => '1233',
                    'convNo' => '',
                ),
                'body' => array(
                    'type' => 'classroom.join',
                    'classroomId' => '12',
                    'title' => '《classroom_name》',
                    'message' => '您被admin添加到班级《classroom_name》',
                ),
            ),
            $this->getQueueService()->getJob()->getBody()
        );
    }

    public function testOnClassroomQuit()
    {
        $this->mockClassroomUser();
        $subscriber = $this->getEventSubscriberWithMockedQueue();
        $subscriber->onClassroomQuit($this->getClassroomEvent());

        $this->assertArrayEquals(
            array(
                'from' => array(
                    'type' => 'classroom',
                    'id' => '12',
                ),
                'to' => array(
                    'type' => 'user',
                    'id' => '1233',
                    'convNo' => '',
                ),
                'body' => array(
                    'type' => 'classroom.quit',
                    'classroomId' => '12',
                    'title' => '《classroom_name》',
                    'message' => '您被admin移出班级《classroom_name》',
                ),
            ),
            $this->getQueueService()->getJob()->getBody()
        );
    }

    public function testOnCourseThreadPostAt()
    {
        $this->mockBiz(
            'Course:ThreadService',
            array(
                array(
                    'functionName' => 'getThread',
                    'withParams' => array(1111, 1),
                    'returnValue' => $this->getThread(),
                ),
            )
        );
        $subscriber = $this->getEventSubscriberWithMockedQueue();
        $subscriber->onCourseThreadPostAt($this->getCourseThreadPostEvent());

        $this->assertArrayEquals(
            array(
                'from' => array(
                    'type' => 'course',
                    'id' => '1111',
                ),
                'to' => array(
                    'type' => 'user',
                    'id' => '333',
                    'convNo' => '',
                ),
                'body' => array(
                    'type' => 'course.thread.post.at',
                    'threadId' => 1,
                    'threadType' => 'question',
                    'courseId' => '1111',
                    'lessonId' => 1234,
                    'questionTitle' => 'thread title',
                    'postContent' => 'this is a content',
                    'title' => '《thread title》',
                    'message' => '《thread title》回复中@了你',
                    'questionCreatedTime' => 1511610055,
                ),
            ),
            $this->getQueueService()->getJob()->getBody()
        );
    }

    public function testOnCourseThreadPostUpdate()
    {
        $this->mockBiz(
            'Course:ThreadService',
            array(
                array(
                    'functionName' => 'getThread',
                    'withParams' => array(1111, 1),
                    'returnValue' => $this->getThread(),
                ),
            )
        );
        $subscriber = $this->getEventSubscriberWithMockedQueue();
        $subscriber->onCourseThreadPostUpdate($this->getCourseThreadPostEvent());

        $this->assertArrayEquals(
            array(
                'from' => array(
                    'type' => 'course',
                    'id' => '1111',
                ),
                'to' => array(
                    'type' => 'user',
                    'id' => 0,
                    'convNo' => '',
                ),
                'body' => array(
                    'type' => 'course.thread.post.update',
                    'threadId' => 1,
                    'threadType' => 'question',
                    'courseId' => '1111',
                    'lessonId' => 1234,
                    'questionTitle' => 'thread title',
                    'postContent' => 'this is a content',
                    'title' => '《thread title》',
                    'message' => '您的问答《thread title》有回复被[admin]编辑',
                    'questionCreatedTime' => 1511610055,
                ),
            ),
            $this->getQueueService()->getJob()->getBody()
        );
    }

    public function testOnCourseThreadPostDelete()
    {
        $this->mockBiz(
            'Course:ThreadService',
            array(
                array(
                    'functionName' => 'getThread',
                    'withParams' => array(1111, 1),
                    'returnValue' => $this->getThread(),
                ),
            )
        );
        $subscriber = $this->getEventSubscriberWithMockedQueue();
        $subscriber->onCourseThreadPostDelete($this->getCourseThreadPostEvent());

        $this->assertArrayEquals(
            array(
                'from' => array(
                    'type' => 'course',
                    'id' => '1111',
                ),
                'to' => array(
                    'type' => 'user',
                    'id' => 0,
                    'convNo' => '',
                ),
                'body' => array(
                    'type' => 'course.thread.post.delete',
                    'threadId' => 1,
                    'threadType' => 'question',
                    'courseId' => '1111',
                    'lessonId' => 1234,
                    'questionTitle' => 'thread title',
                    'postContent' => 'this is a content',
                    'title' => '《thread title》',
                    'message' => '您的问答《thread title》有回复被[admin]删除',
                    'questionCreatedTime' => 1511610055,
                ),
            ),
            $this->getQueueService()->getJob()->getBody()
        );
    }

    public function testOnCourseThreadCreateWithCloudSearchEnabled()
    {
        $this->enableCloudSearchWithImDisabled();
        $subscriber = $this->getEventSubscriberWithMockedQueue();
        $subscriber->onCourseThreadCreate($this->getCourseThreadEvent());

        $this->assertArrayEquals(
            array(
                'type' => 'update',
                'args' => array(
                    'category' => 'thread',
                ),
            ),
            $this->getQueueService()->getJob()->getBody()
        );
    }

    public function testOnCourseThreadCreateWithImEnabled()
    {
        $this->enableImWithCloudSearchDisabled();
        $this->mockCourseInfoForCourseThread();
        $subscriber = $this->getEventSubscriberWithMockedQueue();
        $subscriber->onCourseThreadCreate($this->getCourseThreadEvent());

        $this->assertArrayEquals(
            array(
                'from' => array(
                    'type' => 'course',
                    'id' => '1111',
                ),
                'to' => array(
                    'type' => 'user',
                    'id' => 123456,
                    'convNo' => '',
                ),
                'body' => array(
                    'type' => 'question.created',
                    'threadId' => 1,
                    'title' => '课程提问',
                    'message' => '您的课程有新的提问《thread title》',
                    'courseId' => 1111,
                    'lessonId' => 1234,
                    'questionCreatedTime' => 1511610055,
                    'questionTitle' => 'thread title',
                    'threadType' => 'question',
                ),
            ),
            $this->getQueueService()->getJob()->getBody()
        );
    }

    public function testOnCourseThreadUpdateWithCloudSearchEnabled()
    {
        $this->enableCloudSearchWithImDisabled();
        $subscriber = $this->getEventSubscriberWithMockedQueue();
        $subscriber->onCourseThreadUpdate($this->getCourseThreadEvent());

        $this->assertArrayEquals(
            array(
                'type' => 'update',
                'args' => array(
                    'category' => 'thread',
                ),
            ),
            $this->getQueueService()->getJob()->getBody()
        );
    }

    public function testOnCourseThreadUpdateWithImEnabled()
    {
        $this->enableImWithCloudSearchDisabled();
        $this->mockCourseInfoForCourseThread();
        $subscriber = $this->getEventSubscriberWithMockedQueue();
        $subscriber->onCourseThreadUpdate($this->getCourseThreadEvent());

        $this->assertArrayEquals(
            array(
                'from' => array(
                    'type' => 'course',
                    'id' => '1111',
                ),
                'to' => array(
                    'type' => 'user',
                    'id' => 0,
                    'convNo' => '',
                ),
                'body' => array(
                    'type' => 'course.thread.update',
                    'threadId' => 1,
                    'title' => '《thread title》',
                    'message' => '您的问答《thread title》被[admin]编辑',
                    'courseId' => 1111,
                    'threadType' => 'question',
                ),
            ),
            $this->getQueueService()->getJob()->getBody()
        );
    }

    public function testOnCourseThreadDeleteWithCloudSearchEnabled()
    {
        $this->enableCloudSearchWithImDisabled();
        $subscriber = $this->getEventSubscriberWithMockedQueue();
        $subscriber->onCourseThreadDelete($this->getCourseThreadEvent());

        $this->assertArrayEquals(
            array(
                'type' => 'delete',
                'args' => array(
                    'category' => 'thread',
                    'id' => 'course_1',
                ),
            ),
            $this->getQueueService()->getJob()->getBody()
        );
    }

    public function testOnCourseThreadDeleteWithImEnabled()
    {
        $this->enableImWithCloudSearchDisabled();
        $this->mockCourseInfoForCourseThread();
        $subscriber = $this->getEventSubscriberWithMockedQueue();
        $subscriber->onCourseThreadDelete($this->getCourseThreadEvent());

        $this->assertArrayEquals(
            array(
                'from' => array(
                    'type' => 'course',
                    'id' => '1111',
                ),
                'to' => array(
                    'type' => 'user',
                    'id' => 0,
                    'convNo' => '',
                ),
                'body' => array(
                    'type' => 'course.thread.delete',
                    'threadId' => 1,
                    'title' => '《thread title》',
                    'message' => '您的问答《thread title》被[admin]删除',
                    'courseId' => 1111,
                    'threadType' => 'question',
                ),
            ),
            $this->getQueueService()->getJob()->getBody()
        );
    }

    public function testOnCourseThreadStick()
    {
        $subscriber = $this->getEventSubscriberWithMockedQueue();
        $subscriber->onCourseThreadStick($this->getCourseThreadEvent());

        $this->assertArrayEquals(
            array(
                'from' => array(
                    'type' => 'course',
                    'id' => '1111',
                ),
                'to' => array(
                    'type' => 'user',
                    'id' => 0,
                    'convNo' => '',
                ),
                'body' => array(
                    'type' => 'course.thread.stick',
                    'threadId' => 1,
                    'threadType' => 'question',
                    'title' => '《thread title》',
                    'message' => '您的问答《thread title》被管理员置顶',
                    'courseId' => 1111,
                ),
            ),
            $this->getQueueService()->getJob()->getBody()
        );
    }

    public function testOnCourseThreadUnStick()
    {
        $subscriber = $this->getEventSubscriberWithMockedQueue();
        $subscriber->onCourseThreadUnStick($this->getCourseThreadEvent());

        $this->assertArrayEquals(
            array(
                'from' => array(
                    'type' => 'course',
                    'id' => '1111',
                ),
                'to' => array(
                    'type' => 'user',
                    'id' => 0,
                    'convNo' => '',
                ),
                'body' => array(
                    'type' => 'course.thread.unstick',
                    'threadId' => 1,
                    'threadType' => 'question',
                    'title' => '《thread title》',
                    'message' => '您的问答《thread title》被管理员取消置顶',
                    'courseId' => 1111,
                ),
            ),
            $this->getQueueService()->getJob()->getBody()
        );
    }

    public function testOnCourseThreadUnelite()
    {
        $subscriber = $this->getEventSubscriberWithMockedQueue();
        $subscriber->onCourseThreadUnelite($this->getCourseThreadEvent());

        $this->assertArrayEquals(
            array(
                'from' => array(
                    'type' => 'course',
                    'id' => '1111',
                ),
                'to' => array(
                    'type' => 'user',
                    'id' => 0,
                    'convNo' => '',
                ),
                'body' => array(
                    'type' => 'course.thread.unelite',
                    'threadId' => 1,
                    'threadType' => 'question',
                    'title' => '《thread title》',
                    'message' => '您的问答《thread title》被管理员取消加精',
                    'courseId' => 1111,
                ),
            ),
            $this->getQueueService()->getJob()->getBody()
        );
    }

    public function testOnCourseThreadElite()
    {
        $subscriber = $this->getEventSubscriberWithMockedQueue();
        $subscriber->onCourseThreadElite($this->getCourseThreadEvent());

        $this->assertArrayEquals(
            array(
                'from' => array(
                    'type' => 'course',
                    'id' => '1111',
                ),
                'to' => array(
                    'type' => 'user',
                    'id' => 0,
                    'convNo' => '',
                ),
                'body' => array(
                    'type' => 'course.thread.elite',
                    'threadId' => 1,
                    'threadType' => 'question',
                    'title' => '《thread title》',
                    'message' => '您的问答《thread title》被管理员加精',
                    'courseId' => 1111,
                ),
            ),
            $this->getQueueService()->getJob()->getBody()
        );
    }

    public function testOnCouponUpdate()
    {
        $coupon = array(
            'id' => 1,
            'code' => 'x22232423',
            'type' => 'minus',
            'status' => 'receive',
            'rate' => 10,
            'userId' => 1,
            'deadline' => time(),
            'batchId' => 10,
        );

        $subscriber = $this->getEventSubscriberWithMockedQueue();
        $subscriber->onCouponUpdate(new Event($coupon));
        $result = $this->getQueueService()->getJob()->getBody();

        $this->assertArrayEquals(
            array(
                'type' => 'coupon.receive',
                'couponId' => 1,
                'title' => '获得新的优惠券',
                'message' => '您有一张价值10元的优惠券领取成功',
            ),
            $result['body']
        );
    }

    public function testOnBatchNotificationPublish()
    {
        $fields = array(
            'id' => 1,
            'type' => 'text',
            'fromId' => 1,
            'title' => 'asmd',
            'content' => 'sdncsdn',
            'targetType' => 'global',
            'targetId' => 0,
            'createdTime' => 0,
            'sendedTime' => 0,
            'published' => 0,
        );

        $subscriber = $this->getEventSubscriberWithMockedQueue();
        $subscriber->onBatchNotificationPublish(new Event($fields));
        $result = $this->getQueueService()->getJob()->getBody();

        $this->assertArrayEquals(
            array(
                'type' => 'batch_notification.publish',
                'targetType' => 'batch_notification',
                'targetId' => 1,
                'title' => 'asmd',
                'message' => 'sdncsdn',
                'source' => 'notification',
            ),
            $result['body']
        );
    }

    public function testOnGroupThreadDelete()
    {
        $thread = array(
            'id' => 1,
            'title' => 'title',
            'content' => 'classroom thread content',
            'userId' => 2,
            'targetId' => 1,
            'targetType' => 'classroom',
            'type' => 'question',
            'groupId' => 1,
            'postNum' => 1,
            'hitNum' => 1,
            'createdTime' => time() - 3600,
            'updatedTime' => time(),
        );
        $this->enableCloudSearch();
        $subscriber = $this->getEventSubscriberWithMockedQueue();
        $subscriber->onGroupThreadDelete(new Event($thread));
        $result = $this->getQueueService()->getJob()->getBody();
        $this->assertArrayEquals(
            array(
                'category' => 'thread',
                'id' => 'group_1',
            ),
            $result['args']
        );
    }

    public function testOnOpenCourseDelete()
    {
        $this->enableCloudSearch();
        $course = array(
            'id' => 1,
            'title' => 'liveOpenCourse',
            'type' => 'liveOpen',
            'userId' => 1,
            'createdTime' => time(),
            'smallPicture' => '',
            'middlePicture' => '',
            'largePicture' => '',
            'about' => '',
        );

        $subscriber = $this->getEventSubscriberWithMockedQueue();
        $subscriber->onOpenCourseDelete(new Event($course));
        $result = $this->getQueueService()->getJob()->getBody();
        $this->assertArrayEquals(
            array(
                'category' => 'openCourse',
                'id' => '1',
            ),
            $result['args']
        );
    }

    public function testOnOpenCourseCreate()
    {
        $this->enableCloudSearch();
        $course = array(
            'id' => 1,
            'title' => 'liveOpenCourse',
            'type' => 'liveOpen',
            'userId' => 1,
            'createdTime' => time(),
            'smallPicture' => '',
            'middlePicture' => '',
            'largePicture' => '',
            'about' => '',
        );

        $subscriber = $this->getEventSubscriberWithMockedQueue();
        $subscriber->onOpenCourseCreate(new Event($course));
        $result = $this->getQueueService()->getJob()->getBody();
        $this->assertArrayEquals(
            array(
                'category' => 'openCourse',
            ),
            $result['args']
        );
    }

    public function testOnOpenCourseUpdate()
    {
        $this->enableCloudSearch();
        $course = array(
            'id' => 1,
            'title' => 'liveOpenCourse',
            'type' => 'liveOpen',
            'userId' => 1,
            'status' => 'published',
            'createdTime' => time(),
            'smallPicture' => '',
            'middlePicture' => '',
            'largePicture' => '',
            'about' => '',
        );

        $subscriber = $this->getEventSubscriberWithMockedQueue();
        $subscriber->onOpenCourseUpdate(new Event(array('course' => $course)));
        $result = $this->getQueueService()->getJob()->getBody();

        $this->assertArrayEquals(
            array(
                'category' => 'openCourse',
            ),
            $result['args']
        );
    }

    public function testOnCourseLessonCreate()
    {
        $this->mockBiz(
            'System:SettingService',
            array(
                array(
                    'functionName' => 'get',
                    'returnValue' => array('search_enabled' => true),
                    'withParams' => array('cloud_search', array()),
                ),
                array(
                    'functionName' => 'get',
                    'returnValue' => array('enable' => true),
                    'withParams' => array('mobile'),
                ),
            )
        );
        $taskFields = array(
            'id' => 1,
            'title' => 'test task',
            'mediaType' => 'text',
            'mode' => 'lesson',
            'fromCourseId' => 1,
            'fromCourseSetId' => 1,
            'finishType' => 'time',
            'status' => 'created',
            'type' => 'live',
            'startTime' => time(),
        );

        $subscriber = $this->getEventSubscriberWithMockedQueue();
        $subscriber->onCourseLessonCreate(new Event($taskFields));
        $result = $this->getQueueService()->getJob()->getBody();
        $this->assertArrayEquals(
            array(
                'category' => 'lesson',
            ),
            $result['args']
        );
    }

    public function testOnCourseLessonUpdate()
    {
        $this->mockBiz(
            'System:SettingService',
            array(
                array(
                    'functionName' => 'get',
                    'returnValue' => array('search_enabled' => true),
                    'withParams' => array('cloud_search', array()),
                ),
                array(
                    'functionName' => 'get',
                    'returnValue' => array('enable' => true),
                    'withParams' => array('mobile'),
                ),
            )
        );
        $task = array(
            'id' => 1,
            'title' => 'test task',
            'mediaType' => 'text',
            'mode' => 'lesson',
            'fromCourseId' => 1,
            'fromCourseSetId' => 1,
            'finishType' => 'time',
            'status' => 'published',
            'type' => 'live',
            'startTime' => time(),
        );
        $oldTask = array(
            'id' => 1,
            'title' => 'test task',
            'mediaType' => 'text',
            'mode' => 'lesson',
            'fromCourseId' => 1,
            'fromCourseSetId' => 1,
            'finishType' => 'time',
            'status' => 'created',
            'type' => 'live',
            'startTime' => time(),
        );
        $subscriber = $this->getEventSubscriberWithMockedQueue();
        $subscriber->onCourseLessonUpdate(new Event($task, $oldTask));
        $result = $this->getQueueService()->getJob()->getBody();
        $this->assertArrayEquals(
            array(
                'category' => 'lesson',
            ),
            $result['args']
        );
    }

    public function testOnCourseLessonDelete()
    {
        $this->mockBiz(
            'System:SettingService',
            array(
                array(
                    'functionName' => 'get',
                    'returnValue' => array('search_enabled' => true),
                    'withParams' => array('cloud_search', array()),
                ),
                array(
                    'functionName' => 'get',
                    'returnValue' => array('enable' => true),
                    'withParams' => array('mobile'),
                ),
            )
        );
        $task = array(
            'id' => 1,
            'title' => 'test task',
            'mediaType' => 'text',
            'mode' => 'lesson',
            'fromCourseId' => 1,
            'fromCourseSetId' => 1,
            'finishType' => 'time',
            'status' => 'published',
            'type' => 'live',
            'startTime' => time(),
        );
        $subscriber = $this->getEventSubscriberWithMockedQueue();
        $subscriber->onCourseLessonDelete(new Event($task));
        $result = $this->getQueueService()->getJob()->getBody();
        $this->assertArrayEquals(
            array(
                'category' => 'lesson',
            ),
            $result['args']
        );
    }

    public function testOnClassroomUpdate()
    {
        $this->mockBiz(
            'System:SettingService',
            array(
                array(
                    'functionName' => 'get',
                    'returnValue' => array('search_enabled' => true),
                    'withParams' => array('cloud_search', array()),
                ),
            )
        );
        $textClassroom1 = array(
            'id' => 1,
            'title' => 'test11',
            'status' => 'published',
        );
        $subscriber = $this->getEventSubscriberWithMockedQueue();
        $subscriber->onClassroomUpdate(new Event(array(
            'userId' => 1,
            'classroom' => $textClassroom1,
            'fields' => array(1), )));
        $result1 = $this->getQueueService()->getJob()->getBody();
        $textClassroom2 = array(
            'id' => 1,
            'title' => 'test11',
            'status' => 'closed',
        );
        $subscriber->onClassroomUpdate(new Event(
            array(
                'userId' => 1,
                'classroom' => $textClassroom2,
                'fields' => array(1),
            )
        ));
        $result2 = $this->getQueueService()->getJob()->getBody();
        $this->assertArrayEquals(
            array(
                'type' => 'update',
                'args' => array(
                    'category' => 'classroom',
                ),
            ),
            $result1
        );

        $this->assertArrayEquals(
            array(
                'type' => 'delete',
                'args' => array(
                    'category' => 'classroom',
                ),
            ),
            $result2
        );
    }

    public function testOnUserCreate()
    {
        $this->mockBiz(
            'System:SettingService',
            array(
                array(
                    'functionName' => 'get',
                    'returnValue' => array('search_enabled' => true),
                    'withParams' => array('cloud_search', array()),
                ),
            )
        );
        $user = array(
            'id' => 1,
            'email' => '1234@qq.com',
            'nickname' => 'user1',
            'password' => '123456',
            'confirmPassword' => '123456',
            'createdIp' => '127.0.0.1',
        );
        $subscriber = $this->getEventSubscriberWithMockedQueue();
        $subscriber->onUserCreate(new Event($user));
        $result = $this->getQueueService()->getJob()->getBody();

        $this->assertArrayEquals(
            array(
                'type' => 'update',
                'args' => array(
                    'category' => 'user',
                ),
            ),
            $result
        );
    }

    public function testOnUserDelete()
    {
        $this->mockBiz(
            'System:SettingService',
            array(
                array(
                    'functionName' => 'get',
                    'returnValue' => array('search_enabled' => true),
                    'withParams' => array('cloud_search', array()),
                ),
            )
        );
        $user = array(
            'id' => 1,
            'email' => '1234@qq.com',
            'nickname' => 'user1',
            'password' => '123456',
            'confirmPassword' => '123456',
            'createdIp' => '127.0.0.1',
            'title' => 'title',
            'roles' => array('ROLE_USER', 'ROLE_SUPER_ADMIN'),
            'largeAvatar' => '',
            'updatedTime' => time(),
            'createdTime' => time(),
            'point' => '',
        );
        $subscriber = $this->getEventSubscriberWithMockedQueue();
        $subscriber->onUserDelete(new Event($user));
        $result = $this->getQueueService()->getJob()->getBody();

        $this->assertArrayEquals(
            array(
                'type' => 'delete',
                'args' => array(
                    'category' => 'user',
                ),
            ),
            $result
        );
    }

    public function testOnUserUpdate()
    {
        $this->mockBiz(
            'System:SettingService',
            array(
                array(
                    'functionName' => 'get',
                    'returnValue' => array('search_enabled' => true),
                    'withParams' => array('cloud_search', array()),
                ),
            )
        );
        $user = array(
            'id' => 1,
            'email' => '1234@qq.com',
            'nickname' => 'user1',
            'password' => '123456',
            'confirmPassword' => '123456',
            'createdIp' => '127.0.0.1',
            'title' => 'title',
            'roles' => array('ROLE_USER', 'ROLE_SUPER_ADMIN'),
            'largeAvatar' => '',
            'updatedTime' => time(),
            'createdTime' => time(),
            'point' => '',
        );
        $subscriber = $this->getEventSubscriberWithMockedQueue();
        $subscriber->onUserUpdate(new Event(array('user' => $user, 'fields' => array('nickname' => 'user3'))));
        $result = $this->getQueueService()->getJob()->getBody();

        $this->assertArrayEquals(
            array(
                'type' => 'update',
                'args' => array(
                    'category' => 'user',
                ),
            ),
            $result
        );
    }

    public function testOnInviteReward()
    {
        $coupon = array(
            'id' => 1,
            'code' => 'x22232423',
            'type' => 'minus',
            'status' => 'receive',
            'rate' => 10,
            'userId' => 1,
            'deadline' => time(),
            'batchId' => 10,
        );
        $subscriber = $this->getEventSubscriberWithMockedQueue();
        $subscriber->onInviteReward(new Event($coupon, array('message' => array('rewardName' => 'rewardName', 'settingName' => 'settingName'))));
        $result = $this->getQueueService()->getJob()->getBody();

        $this->assertArrayEquals(
            array(
                'type' => 'invite.reward',
                'userId' => 1,
                'title' => 'rewardName',
                'message' => '恭喜您获得rewardName奖励，settingName元面值抵价优惠券一张，已发至您的账户',
            ),
            $result['body']
        );
    }

    public function testOnCourseReviewAdd()
    {
        $this->mockBiz(
            'Course:CourseService',
            array(
                array(
                    'functionName' => 'getCourse',
                    'returnValue' => array('title' => 'title', 'id' => 1),
                    'withParams' => array(1),
                ),
            )
        );
        $this->mockBiz(
            'Course:ReviewService',
            array(
                array(
                    'functionName' => 'getReview',
                    'returnValue' => array('userId' => 1, 'id' => 1),
                    'withParams' => array(1),
                ),
            )
        );
        $subscriber = $this->getEventSubscriberWithMockedQueue();
        $subscriber->onCourseReviewAdd(new Event(array('content' => 'content', 'id' => 1, 'courseId' => 1, 'parentId' => 1)));
        $result = $this->getQueueService()->getJob()->getBody();

        $this->assertArrayEquals(
            array(
                'type' => 'course.review_add',
                'courseId' => 1,
                'reviewId' => 1,
                'parentReviewId' => 1,
                'title' => '您在课程title的评价已被回复',
                'message' => 'content',
            ),
            $result['body']
        );
    }

    public function testOnClassroomReviewAdd()
    {
        $this->mockBiz(
            'Classroom:ClassroomService',
            array(
                array(
                    'functionName' => 'getClassroom',
                    'returnValue' => array('title' => 'title', 'id' => 1),
                    'withParams' => array(1),
                ),
            )
        );
        $this->mockBiz(
            'Course:ReviewService',
            array(
                array(
                    'functionName' => 'getReview',
                    'returnValue' => array('userId' => 1, 'id' => 1),
                    'withParams' => array(1),
                ),
            )
        );
        $subscriber = $this->getEventSubscriberWithMockedQueue();
        $subscriber->onClassroomReviewAdd(new Event(array('content' => 'content', 'id' => 1, 'classroomId' => 1, 'parentId' => 1)));
        $result = $this->getQueueService()->getJob()->getBody();

        $this->assertArrayEquals(
            array(
                'type' => 'classroom.review_add',
                'classroomId' => 1,
                'reviewId' => 1,
                'parentReviewId' => 1,
                'title' => '您在班级title的评价已被回复',
                'message' => 'content',
            ),
            $result['body']
        );
    }

    public function testOnAnnouncementCreate()
    {
        $announcementInfo = array(
            'id' => 1,
            'targetType' => 'global',
            'targetId' => '1',
            'content' => 'test_announcement',
            'startTime' => time(),
            'endTime' => time() + 3600 * 1000,
            'url' => 'http://www.baidu.com',
            'notify' => 1,
        );

        $subscriber = $this->getEventSubscriberWithMockedQueue();
        $subscriber->onAnnouncementCreate(new Event($announcementInfo));
        $result = $this->getQueueService()->getJob()->getBody();

        $this->assertArrayEquals(
            array(
                'type' => 'announcement.create',
                'id' => 1,
                'targetType' => 'announcement',
                'title' => 'test_announcement',
                'targetId' => 1,
            ),
            $result['body']
        );
    }

    public function testOnThreadCreate()
    {
        $this->mockBiz(
            'System:SettingService',
            array(
                array(
                    'functionName' => 'get',
                    'returnValue' => array('search_enabled' => true),
                    'withParams' => array('cloud_search', array()),
                ),
                array(
                    'functionName' => 'get',
                    'returnValue' => array('enabled' => true),
                    'withParams' => array('app_im', array()),
                ),
            )
        );
        $thread = array(
            'updateTime' => time(),
            'createdTime' => time(),
            'questionType' => 'questionType',
            'hitNum' => 1,
            'postNum' => 1,
            'relationId' => 1,
            'id' => 1,
            'title' => 'title',
            'content' => 'thread content',
            'userId' => 2,
            'targetId' => 1,
            'targetType' => 'course',
            'type' => 'question',
    );

        $subscriber = $this->getEventSubscriberWithMockedQueue();
        $subscriber->onThreadCreate(new Event($thread));
        $result = $this->getQueueService()->getJob()->getBody();

        $this->assertArrayEquals(
            array(
                'type' => 'update',
                'args' => array(
                    'category' => 'thread',
                ),
            ),
            $result
        );
    }

    public function testOnGroupThreadCreate()
    {
        $this->mockBiz(
            'System:SettingService',
            array(
                array(
                    'functionName' => 'get',
                    'returnValue' => array('search_enabled' => true),
                    'withParams' => array('cloud_search', array()),
                ),
                array(
                    'functionName' => 'get',
                    'returnValue' => array('enabled' => true),
                    'withParams' => array('app_im', array()),
                ),
            )
        );
        $thread = array(
            'updateTime' => time(),
            'createdTime' => time(),
            'questionType' => 'questionType',
            'hitNum' => 1,
            'postNum' => 1,
            'relationId' => 1,
            'id' => 1,
            'title' => 'title',
            'content' => 'thread content',
            'userId' => 2,
            'targetId' => 1,
            'targetType' => 'group',
            'type' => 'group',
            'groupId' => 1,
        );

        $subscriber = $this->getEventSubscriberWithMockedQueue();
        $subscriber->onGroupThreadCreate(new Event($thread));
        $result = $this->getQueueService()->getJob()->getBody();

        $this->assertArrayEquals(
            array(
                'type' => 'update',
                'args' => array(
                    'category' => 'thread',
                ),
            ),
            $result
        );
    }

    public function testOnThreadPostCreate()
    {
        $post = array(
            'id' => 1,
            'userId' => 1,
            'courseId' => 1,
            'threadId' => 1,
            'content' => 'post thread',
            'targetId' => 1,
            'targetType' => 'course',
            'type' => 'course',
            'groupId' => 1,
            'updateTime' => time(),
            'createdTime' => time(),
        );
        $thread = array(
            'updateTime' => time(),
            'createdTime' => time(),
            'questionType' => 'questionType',
            'hitNum' => 1,
            'postNum' => 1,
            'relationId' => 1,
            'id' => 1,
            'title' => 'title',
            'content' => 'thread content',
            'userId' => 2,
            'targetId' => 1,
            'targetType' => 'course',
            'type' => 'question',
            'groupId' => 1,
        );
        $this->mockBiz(
            'Thread:ThreadService',
            array(
                array(
                    'functionName' => 'getThread',
                    'returnValue' => $thread,
                    'withParams' => array(1),
                ),
            )
        );

        $this->mockBiz(
            'Course:CourseSetService',
            array(
                array(
                    'functionName' => 'getCourseSet',
                    'returnValue' => array('title' => '1', 'id' => 1, 'teacherIds' => array(1)),
                    'withParams' => array(1),
                ),
            )
        );

        $this->mockBiz(
            'Course:CourseService',
            array(
                array(
                    'functionName' => 'getCourse',
                    'returnValue' => array('title' => '1', 'id' => 1, 'courseSetId' => 1, 'teacherIds' => array(1)),
                    'withParams' => array(1),
                ),
            )
        );

        $subscriber = $this->getEventSubscriberWithMockedQueue();
        $subscriber->onThreadPostCreate(new Event($post));
        $result = $this->getQueueService()->getJob()->getBody();

        $this->assertEquals('question.answered', $result['body']['type']);
    }

    public function testOnCourseThreadPostCreate()
    {
        $post = array(
            'id' => 1,
            'userId' => 1,
            'courseId' => 1,
            'threadId' => 1,
            'content' => 'post thread',
            'targetId' => 1,
            'targetType' => 'course',
            'type' => 'course',
            'updateTime' => time(),
            'createdTime' => time(),
        );
        $thread = array(
            'updateTime' => time(),
            'createdTime' => time(),
            'questionType' => 'questionType',
            'hitNum' => 1,
            'postNum' => 1,
            'relationId' => 1,
            'id' => 1,
            'title' => 'title',
            'content' => 'thread content',
            'userId' => 2,
            'targetId' => 1,
            'targetType' => 'course',
            'type' => 'question',
            'courseId' => 1,
            'taskId' => 1,
        );
        $this->mockBiz(
            'Course:ThreadService',
            array(
                array(
                    'functionName' => 'getThread',
                    'returnValue' => $thread,
                    'withParams' => array(1, 1),
                ),
            )
        );

        $this->mockBiz(
            'Course:CourseSetService',
            array(
                array(
                    'functionName' => 'getCourseSet',
                    'returnValue' => array('title' => '1', 'id' => 1, 'teacherIds' => array(1)),
                    'withParams' => array(1),
                ),
            )
        );

        $this->mockBiz(
            'Course:CourseService',
            array(
                array(
                    'functionName' => 'getCourse',
                    'returnValue' => array('title' => '1', 'id' => 1, 'courseSetId' => 1, 'teacherIds' => array(1)),
                    'withParams' => array(1),
                ),
            )
        );

        $subscriber = $this->getEventSubscriberWithMockedQueue();
        $subscriber->onCourseThreadPostCreate(new Event($post));
        $result = $this->getQueueService()->getJob()->getBody();

        $this->assertEquals('question.answered', $result['body']['type']);
    }

    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    protected function getQueueService()
    {
        return $this->createService('Queue:QueueService');
    }

    private function createArticleTestData($isCloudSearchOn)
    {
        $this->mockBiz(
            'System:SettingService',
            array(
                array(
                    'functionName' => 'get',
                    'returnValue' => array('about' => 'aboutSchool', 'logo' => 'logo.png'),
                    'withParams' => array('mobile'),
                ),
                array(
                    'functionName' => 'get',
                    'returnValue' => array('slogan' => 'slogan'),
                    'withParams' => array('site'),
                ),
                array(
                    'functionName' => 'get',
                    'returnValue' => array('enabled' => true),
                    'withParams' => array('app_im', array()),
                ),
                array(
                    'functionName' => 'get',
                    'returnValue' => array('search_enabled' => $isCloudSearchOn),
                    'withParams' => array('cloud_search', array()),
                ),
            )
        );

        $subscriber = $this->getEventSubscriberWithMockedQueue();
        $subscriber->onArticleCreate($this->getArticleEvent());
    }

    private function enableImWithCloudSearchDisabled()
    {
        $this->mockBiz(
            'System:SettingService',
            array(
                array(
                    'functionName' => 'get',
                    'returnValue' => array('enabled' => true),
                    'withParams' => array('app_im', array()),
                ),
                array(
                    'functionName' => 'get',
                    'returnValue' => array('search_enabled' => false),
                    'withParams' => array('cloud_search', array()),
                ),
            )
        );
    }

    private function enableCloudSearchWithImDisabled()
    {
        $this->mockBiz(
            'System:SettingService',
            array(
                array(
                    'functionName' => 'get',
                    'returnValue' => array('enabled' => false),
                    'withParams' => array('app_im', array()),
                ),
                array(
                    'functionName' => 'get',
                    'returnValue' => array('search_enabled' => true),
                    'withParams' => array('cloud_search', array()),
                ),
            )
        );
    }

    private function enableIm()
    {
        $this->mockBiz(
            'System:SettingService',
            array(
                array(
                    'functionName' => 'get',
                    'returnValue' => array('enabled' => true),
                    'withParams' => array('app_im', array()),
                ),
            )
        );
    }

    private function enableCloudSearch()
    {
        $this->mockBiz(
            'System:SettingService',
            array(
                array(
                    'functionName' => 'get',
                    'returnValue' => array('search_enabled' => true),
                    'withParams' => array('cloud_search', array()),
                ),
            )
        );
    }

    private function mockCourseInfoForCourseThread()
    {
        $this->mockBiz(
            'Course:CourseService',
            array(
                array(
                    'functionName' => 'getCourse',
                    'withParams' => array(1111),
                    'returnValue' => array('teacherIds' => array(123456), 'courseSetId' => 13579, 'title' => 'course title'),
                ),
            )
        );

        $this->mockBiz(
            'Course:CourseSetService',
            array(
                array(
                    'functionName' => 'getCourseSet',
                    'withParams' => array(13579),
                    'returnValue' => array('cover' => array('small' => 'smallPic.png')),
                ),
            )
        );
    }

    private function mockUserInfo()
    {
        $this->mockBiz(
            'User:UserService',
            array(
                array(
                    'functionName' => 'getUser',
                    'returnValue' => array('id' => 1, 'nickname' => '用户1'),
                    'withParams' => array(1),
                ),
                array(
                    'functionName' => 'getUser',
                    'returnValue' => array('id' => 2, 'nickname' => '用户2'),
                    'withParams' => array(2),
                ),
            )
        );
    }

    private function mockClassroomUser()
    {
        $this->mockBiz(
            'User:UserService',
            array(
                array(
                    'functionName' => 'getUser',
                    'withParams' => array('1233'),
                    'returnValue' => array(
                        'point' => '123',
                        'roles' => '|ROLE_USER|',
                        'id' => 1233,
                        'nickname' => 'user_test',
                        'title' => 'user_title',
                        'largeAvatar' => 'largeAvatar.png',
                        'updatedTime' => time(),
                        'createdTime' => time(),
                    ),
                ),
            )
        );
    }

    private function getUserEvent()
    {
        $friendsInfo = array(
            'fromId' => 1,
            'toId' => 2,
        );

        return new Event($friendsInfo);
    }

    private function getEventSubscriberWithMockedQueue()
    {
        $biz = $this->getBiz();
        $biz['@Queue:QueueService'] = new MockedQueueServiceImpl();

        return new PushMessageEventSubscriber($this->biz);
    }

    private function getArticleEvent()
    {
        $article = array(
            'thumb' => 'thumb.png',
            'originalThumb' => 'originalThumb.png',
            'picture' => 'picture.png',
            'title' => 'article title',
            'id' => 123,
        );

        return new Event($article);
    }

    private function getClassroomEvent()
    {
        $classroom = array(
            'smallPicture' => 'smallPicture.png',
            'middlePicture' => 'middlePicture.png',
            'largePicture' => 'largePicture.png',
            'about' => 'about content',
            'id' => '12',
            'title' => 'classroom_name',
        );
        $memberInfo = array(
            'userId' => '1233',
            'member' => array(),
        );

        return new Event($classroom, $memberInfo);
    }

    private function getCourseThreadPostEvent()
    {
        $threadPost = array(
            'courseId' => '1111',
            'threadId' => 1,
            'id' => 1311,
            'content' => 'this is a content',
            'userId' => 333,
            'targetType' => 'course',
            'targetId' => '1111',
            'thread' => array(),
            'createdTime' => 1511610055,
        );

        $arguments = array(
            'users' => array(
                array(
                    'id' => 333,
                ),
            ),
        );

        return new Event($threadPost, $arguments);
    }

    private function getCourseThreadEvent()
    {
        return new Event($this->getThread());
    }

    private function getThread()
    {
        return array(
            'courseId' => 1111,
            'taskId' => 1234,
            'id' => 1,
            'targetType' => 'course',
            'targetId' => 1111,
            'target' => array(
                'type' => 'course',
            ),
            'content' => 'this is a content',
            'postNum' => 333,
            'hitNum' => 222,
            'updateTime' => 1511610055,
            'createdTime' => 1511610055,
            'title' => 'thread title',
            'type' => 'question',
        );
    }

    private function getClassroomService()
    {
        return $this->createService('Classroom:ClassroomService');
    }

    /**
     * @return CourseSetServiceImpl
     */
    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }
}
