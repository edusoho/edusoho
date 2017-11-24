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
        list($subscriber, $event) = $this->createArticleTestData(true);
        $subscriber->onArticleCreate($event);

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
        list($subscriber, $event) = $this->createArticleTestData(false);
        $subscriber->onArticleCreate($event);

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

    public function testOnUserFollow()
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

        $subscriber = $this->getEventSubscriberWithMockedQueue();

        $friendsInfo = array(
            'fromId' => 1,
            'toId' => 2,
        );
        $event = new Event($friendsInfo);
        $subscriber->onUserFollow($event);

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

        $article = array(
            'thumb' => 'thumb.png',
            'originalThumb' => 'originalThumb.png',
            'picture' => 'picture.png',
            'title' => 'article title',
            'id' => 123,
        );
        $event = new Event($article);

        return array($subscriber, $event);
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

    private function getEventSubscriberWithMockedQueue()
    {
        $biz = $this->getBiz();
        $biz['@Queue:QueueService'] = new MockedQueueServiceImpl();

        return new PushMessageEventSubscriber($this->biz);
    }
}
