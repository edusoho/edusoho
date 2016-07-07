<?php
namespace Topxia\Service\Card\Event;

use Topxia\Service\Common\ServiceEvent;
use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'coupon.use'         => 'onCouponUsed',
            'order.service.paid' => 'onOrderPaid',
            'user.register'      => 'onUserRegister'
        );
    }

    public function onCouponUsed(ServiceEvent $event)
    {
        $coupon = $event->getSubject();
        $card   = $this->getCardService()->getCardByCardIdAndCardType($coupon['id'], 'coupon');

        if (!empty($card)) {
            $this->getCardService()->updateCardByCardIdAndCardType($coupon['id'], 'coupon', array(
                'status'  => 'used',
                'useTime' => $coupon['orderTime']
            ));
        }
    }

    public function onOrderPaid(ServiceEvent $event)
    {
        $order         = $event->getSubject();
        $inviteSetting = $this->getSettingService()->get('invite', array());

        if (isset($inviteSetting['get_coupon_setting']) && $inviteSetting['get_coupon_setting'] == 1) {
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
    }

    public function onUserRegister(ServiceEvent $event)
    {
        $userIds      = $event->getSubject();
        $userId       = $userIds['userId'];
        $inviteUserId = $userIds['inviteUserId'];

        $inviteSetting = $this->getSettingService()->get('invite', array());

        if (isset($inviteSetting['get_coupon_setting']) && $inviteSetting['get_coupon_setting'] == 0) {
            $inviteCoupon = $this->getCouponService()->generateInviteCoupon($inviteUserId, 'pay');

            if (!empty($inviteCoupon)) {
                $this->getInviteRecordService()->addInviteRewardRecordToInvitedUser($userId, array('inviteUserCardId' => $inviteCoupon['id']));
            }
        }
    }

    private function getCardService()
    {
        return ServiceKernel::instance()->createService('Card.CardService');
    }

    protected function getInviteRecordService()
    {
        return ServiceKernel::instance()->createService('User.InviteRecordService');
    }

    protected function getCouponService()
    {
        return ServiceKernel::instance()->createService('Coupon.CouponService');
    }

    protected function getSettingService()
    {
        return ServiceKernel::instance()->createService('System.SettingService');
    }

    protected function getUserService()
    {
        return ServiceKernel::instance()->createService('User.UserService');
    }
}
