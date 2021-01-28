<?php

namespace Tests\Unit\User\Event;

use Biz\BaseTestCase;
use Biz\User\Event\VipMemberEventSubscriber;
use Codeages\Biz\Framework\Event\Event;

class VipMemberEventSubscriberTest extends BaseTestCase
{
    public function testGetSubscribedEvent()
    {
        $this->assertEquals(array(
            'admin.operate.vip_member' => 'onOperateVipMember',
        ), VipMemberEventSubscriber::getSubscribedEvents());
    }

    public function testOnOperateVipMember()
    {
        $service = $this->mockBiz('User:UserService', array(
            array(
                'functionName' => 'updateUserUpdatedTime',
            ),
        ));
        $event = new Event(array(
            'userId' => 1,
        ));

        $eventSubscriber = new VipMemberEventSubscriber($this->biz);
        $eventSubscriber->onOperateVipMember($event);

        $service->shouldHaveReceived('updateUserUpdatedTime')->times(1);
    }
}
