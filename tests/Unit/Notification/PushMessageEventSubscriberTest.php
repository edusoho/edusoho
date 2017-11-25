<?php

namespace Tests\Unit\Notification;

use Biz\BaseTestCase;
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
                    'threadType' => 'none',
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
                    'threadType' => 'none',
                    'title' => '《thread title》',
                    'message' => '您的《thread title》被管理员置顶',
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
                    'threadType' => 'none',
                    'title' => '《thread title》',
                    'message' => '您的《thread title》被管理员取消置顶',
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
                    'threadType' => 'none',
                    'title' => '《thread title》',
                    'message' => '您的《thread title》被管理员取消加精',
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
                    'threadType' => 'none',
                    'title' => '《thread title》',
                    'message' => '您的《thread title》被管理员加精',
                    'courseId' => 1111,
                ),
            ),
            $this->getQueueService()->getJob()->getBody()
        );
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
            'content' => 'this is a content',
            'postNum' => 333,
            'hitNum' => 222,
            'updateTime' => 1511610055,
            'createdTime' => 1511610055,
            'title' => 'thread title',
        );
    }
}
