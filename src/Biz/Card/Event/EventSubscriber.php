<?php
namespace Biz\Card\Event;

use Biz\Card\Service\CardService;
use Biz\System\Service\SettingService;
use Biz\User\Service\InviteRecordService;
use Biz\User\Service\UserService;
use Codeages\Biz\Framework\Event\Event;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Topxia\Service\Common\ServiceKernel;

class EventSubscriber extends \Codeages\PluginBundle\Event\EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'coupon.use'         => 'onCouponUsed',
            'order.service.paid' => 'onOrderPaid',
            'user.register'      => 'onUserRegister'
        );
    }

    public function onCouponUsed(Event $event)
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

    public function onOrderPaid(Event $event)
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

    public function onUserRegister(Event $event)
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

    /**
     * @return CardService
     */
    private function getCardService()
    {
        return $this->createService('Card:CardService');
    }

    /**
     * @return InviteRecordService
     */
    protected function getInviteRecordService()
    {
        return $this->createService('User:InviteRecordService');
    }

    protected function getCouponService()
    {
        return $this->createService('Coupon:CouponService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    protected function createService($name)
    {
        return $this->getBiz()->service($name);
    }
}
