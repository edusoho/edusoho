<?php

namespace Biz\OrderFacade\Event;

use Biz\Coupon\Service\CouponService;
use Biz\System\Service\SettingService;
use Biz\User\Service\InviteRecordService;
use Codeages\Biz\Framework\Event\Event;
use Codeages\PluginBundle\Event\EventSubscriber;

class OrderEventSubscriber extends EventSubscriber
{
    public static function getSubscribedEvents()
    {
        return array(
            'order.paid' => 'onOrderPaid',
        );
    }

    public function onOrderPaid(Event $event)
    {
        $order = $event->getSubject();
        $this->inviteReward($order);
    }

    private function inviteReward($order)
    {
        $inviteSetting = $this->getSettingService()->get('invite', array());

        if (isset($inviteSetting['get_coupon_setting'])
            && $inviteSetting['get_coupon_setting'] == 1
            && $order['pay_amount'] > 0) {
            $record = $this->getInviteRecordService()->getRecordByInvitedUserId($order['user_id']);

            if (!empty($record) && empty($record['inviteUserCardId'])) {
                $inviteCoupon = $this->getCouponService()->generateInviteCoupon($record['inviteUserId'], 'pay');

                if (!empty($inviteCoupon)) {
                    $this->getInviteRecordService()->addInviteRewardRecordToInvitedUser($order['user_id'], array(
                        'inviteUserCardId' => $inviteCoupon['id'],
                    ));
                }
            }
        }
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->getBiz()->service('System:SettingService');
    }

    /**
     * @return CouponService
     */
    protected function getCouponService()
    {
        return $this->getBiz()->service('Coupon:CouponService');
    }

    /**
     * @return InviteRecordService
     */
    protected function getInviteRecordService()
    {
        return $this->getBiz()->service('User:InviteRecordService');
    }
}
