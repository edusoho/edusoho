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

        if ($this->isFirstOrderByUserId($order['userId'])) {
            $inviteRecord = $this->getInviteRecordService()->getRecordByInvitedUserId($order['userId']);

            if (!empty($inviteRecord)) {
                $inviteCoupon = $this->getCouponService()->generateInviteCoupon($inviteRecord['inviteUserId'], 'pay');

                if (!empty($inviteCoupon)) {
                    $this->getInviteRecordService()->addInviteRewardRecordToInvitedUser($order['userId'], array('inviteUserCardId' => $inviteCoupon['id']));
                }
            }
        }
    }

    private function isFirstOrderByUserId($userId)
    {
        $record = $this->getInviteRecordService()->getRecordByInvitedUserId($userId);

        $conditionsAmount = array(
            'userId'                 => $userId,
            'amount'                 => 0.00,
            'status'                 => 'paid',
            'createdTimeGreaterThan' => $record['inviteTime'] ? $record['inviteTime'] : null
        );
        $conditionsCoinAmount = array(
            'userId'                 => $userId,
            'coinAmount'             => 0.00,
            'status'                 => 'paid',
            'createdTimeGreaterThan' => $record['inviteTime'] ? $record['inviteTime'] : null
        );

        $orderAmount     = $this->getOrderService()->searchOrders($conditionsAmount, array('createdTime', 'DESC'), 0, 2);
        $orderCoinAmount = $this->getOrderService()->searchOrders($conditionsCoinAmount, array('createdTime', 'DESC'), 0, 2);

        if (count($orderAmount) + count($orderCoinAmount) == 1) {
            return true;
        } else {
            return false;
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

    protected function getOrderService()
    {
        return ServiceKernel::instance()->createService('Order.OrderService');
    }
}
