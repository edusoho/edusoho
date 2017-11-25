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
        $this->enableIm();
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
        $this->enableIm();
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
}
