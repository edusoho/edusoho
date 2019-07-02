<?php

namespace Tests\Unit\Card\Event;

use Biz\BaseTestCase;
use Biz\Card\Event\EventSubscriber;
use Biz\User\Service\InviteRecordService;
use Codeages\Biz\Framework\Event\Event;

class EventSubscriberTest extends BaseTestCase
{
    public function testGetSubscribedEvents()
    {
        $eventSubscriber = new EventSubscriber($this->biz);

        $expected = array(
            'order.service.paid' => 'onOrderPaid',
            'user.register' => 'onUserRegister',
        );
        $result = $eventSubscriber::getSubscribedEvents();
        $this->assertEquals($expected, $result);
    }

    public function testOnOrderPaid()
    {
        $event = new Event(array(
            'coinAmount' => 1,
            'amount' => 2,
            'userId' => 2,
        ));
        $this->mockSettingAndCoupon(1);
        $this->getInviteRecordService()->createInviteRecord(1, 2);

        $eventSubscriber = new EventSubscriber($this->biz);
        $eventSubscriber->onOrderPaid($event);

        $result = $this->getInviteRecordService()->getRecordByInvitedUserId(2);

        $this->assertEquals(1, $result['inviteUserCardId']);
    }

    public function testOnUserRegister()
    {
        $event = new Event(array(
            'userId' => 2,
            'inviteUserId' => 1,
        ));

        $this->mockSettingAndCoupon(0);
        $this->getInviteRecordService()->createInviteRecord(1, 2);

        $eventSubscriber = new EventSubscriber($this->biz);
        $eventSubscriber->onUserRegister($event);

        $record = $this->getInviteRecordService()->getRecordByInvitedUserId(2);
        $this->assertEquals(1, $record['inviteUserCardId']);
    }

    private function mockSettingAndCoupon($couponSettingValue)
    {
        $this->mockBiz('System:SettingService', array(
            array(
                'functionName' => 'get',
                'returnValue' => array(
                    'get_coupon_setting' => $couponSettingValue,
                ),
                'withParams' => array('invite', array()),
            ),
        ));

        $this->mockBiz('Coupon:CouponService', array(
            array(
                'functionName' => 'generateInviteCoupon',
                'returnValue' => array(
                    'id' => 1,
                ),
            ),
        ));
    }

    /**
     * @return InviteRecordService
     */
    private function getInviteRecordService()
    {
        return $this->createService('User:InviteRecordService');
    }
}
