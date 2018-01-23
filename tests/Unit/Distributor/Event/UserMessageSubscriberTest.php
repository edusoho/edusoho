<?php

namespace Tests\Unit\Distributor\Event;

use Biz\BaseTestCase;
use Biz\Distributor\Event\UserMessageSubscriber;
use Codeages\Biz\Framework\Event\Event;

class UserMessageSubscriberTest extends BaseTestCase
{
    public function testGetSubscribedEvents()
    {
        $subscriber = new UserMessageSubscriber($this->getBiz());
        $this->assertArrayEquals(
            array(
                'user.change_nickname' => 'onChangeUserMessage',
                'user.change_mobile' => 'onChangeUserMessage',
            ), $subscriber->getSubscribedEvents()
        );
    }

    public function testOnChangeUserMessage()
    {
        $userService = $this->mockBiz(
            'User:UserService',
            array(
                array(
                    'functionName' => 'getUser',
                    'withParams' => array(2),
                    'returnValue' => array('name' => 'test', 'type' => 'distributor', 'distributorToken' => 'test'),
                ),
            )
        );

        $distributorUserService = $this->mockBiz(
            'Distributor:DistributorUserService',
            array(
                array(
                    'functionName' => 'createJobData',
                    'withParams' => array(array('name' => 'test', 'type' => 'distributor', 'distributorToken' => 'test', 'token' => 'test')),
                ),
            )
        );
        $event = new Event(
            array(
                'id' => 2,
            )
        );

        $subscriber = new UserMessageSubscriber($this->getBiz());
        $result = $subscriber->onChangeUserMessage($event);

        $this->assertNull($result);
        $userService->shouldHaveReceived('getUser')->times(1);
        $distributorUserService->shouldHaveReceived('createJobData')->times(1);
    }
}
