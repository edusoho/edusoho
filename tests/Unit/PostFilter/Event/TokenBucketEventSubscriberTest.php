<?php

namespace Tests\Unit\PostFilter\Event;

use Biz\BaseTestCase;
use Biz\PostFilter\Event\TokenBucketEventSubscriber;
use Biz\User\CurrentUser;
use Codeages\Biz\Framework\Event\Event;

class TokenBucketEventSubscriberTest extends BaseTestCase
{
    public function testGetSubscribedEvent()
    {
        $expected = array(
            'thread.before_create' => 'before',
            'thread.create' => 'incrToken',
            'thread.post.before_create' => 'before',
            'thread.post.create' => 'incrToken',
            'course.thread.before_create' => 'before',
            'course.thread.create' => 'incrToken',
            'course.thread.post.before_create' => 'before',
            'course.thread.post.create' => 'incrToken',
            'group.thread.before_create' => 'before',
            'group.thread.create' => 'incrToken',
            'group.thread.post.before_create' => 'before',
            'group.thread.post.create' => 'incrToken',
        );

        $this->assertEquals($expected, TokenBucketEventSubscriber::getSubscribedEvents());
    }

    public function testBeforeWithManageRole()
    {
        $event = new Event(array());

        $eventSubscriber = new TokenBucketEventSubscriber($this->biz);

        $this->mockBiz('PostFilter:TokenBucketService', array(
            array(
                'functionName' => 'hasToken',
                'times' => 1
            )
        ));
        $eventSubscriber->before($event);

        $this->getTokenBucketService()->shouldNotHaveReceived('hasToken');
    }

    public function testBefore()
    {
        $event = new Event(array());

        $eventSubscriber = new TokenBucketEventSubscriber($this->biz);

        $this->mockBiz('PostFilter:TokenBucketService', array(
            array(
                'functionName' => 'hasToken',
                'times' => 1,
                'withParams' => array('127.0.0.1', 'thread'),
                'returnValue' => false
            ),
            array(
                'functionName' => 'hasToken',
                'times' => 1,
                'withParams' => array(3, 'threadLoginedUser'),
                'returnValue' => true
            )
        ));

        $currentUser = new CurrentUser();
        $currentUser->fromArray(array(
            'id' => 3,
            'nickname' => 'testUser',
            'currentIp' => '127.0.0.1',
            'roles' => array('ROLE_USER'),
        ));

        $eventSubscriber->before($event);

        $this->getTokenBucketService()->shouldHaveReceiver('hasToken')->times(1);
        $this->assertTrue($event->isPropagationStopped());
    }


    private function getTokenBucketService()
    {
        return $this->createService('PostFilter:TokenBucketService');
    }
}