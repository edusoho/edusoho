<?php

namespace Tests\Unit\Distributor\Event;

use Biz\BaseTestCase;
use Biz\Distributor\Event\UserSubscriber;
use Codeages\Biz\Framework\Event\Event;

class UserSubscriberTest extends BaseTestCase
{
    public function testGetSubscribedEvents()
    {
        $subscriber = new UserSubscriber($this->getBiz());
        $this->assertArrayEquals(
            array(
                'user.change_nickname' => 'onChangeUser',
                'user.change_mobile' => 'onChangeUser',
            ), $subscriber->getSubscribedEvents()
        );
    }

    public function testOnChangeUser()
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

        $subscriber = new UserSubscriber($this->getBiz());
        $result = $subscriber->onChangeUser($event);

        $this->assertNull($result);
        $userService->shouldHaveReceived('getUser')->times(1);
        $distributorUserService->shouldHaveReceived('createJobData')->times(1);
    }
}
