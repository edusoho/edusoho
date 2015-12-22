<?php
namespace Topxia\Service\User\Event;

use Topxia\Service\Common\ServiceEvent;
use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class OrderEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'order.service.paid' => 'onOrderPaid'
        );
    }

    public function onOrderPaid(ServiceEvent $event)
    {
        $order = $event->getSubject();

        if ($order['coinAmount'] > 0 || $order['amount'] > 0) {
            $record = $this->getInviteRecordService()->getRecordByInvitedUserId($order['userId']);

            if (!empty($record) && $record['inviteUserCardId'] == null) {
                $inviteCoupon = $this->getCouponService()->generateInviteCoupon($record['inviteUserId'], 'pay');

                if (!empty($inviteCoupon)) {
                    $this->getInviteRecordService()->addInviteRewardRecordToInvitedUser($order['userId'], array('inviteUserCardId' => $inviteCoupon['id']));
                }
            }
        }
    }

    protected function getInviteRecordService()
    {
        return ServiceKernel::instance()->createService('User.InviteRecordService');
    }

    protected function getCouponService()
    {
        return ServiceKernel::instance()->createService('Coupon.CouponService');
    }
}
