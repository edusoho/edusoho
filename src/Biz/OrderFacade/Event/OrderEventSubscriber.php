<?php

namespace Biz\OrderFacade\Event;

use Biz\Coupon\Service\CouponService;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\MemberService;
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
            'order.apply_refund' => 'onOrderApplyRefund'
        );
    }

    public function onOrderPaid(Event $event)
    {
        $order = $event->getSubject();
        $this->inviteReward($order);
    }

    public function onOrderApplyRefund(Event $event)
    {
        $order = $event->getSubject();
        $user = $this->getBiz()->offsetGet('user');
        $item = $event->getArgument('orderItem');
        if ($item['target_type'] == 'course') {
            $course = $this->getCourseService()->getCourse($item['target_id']);
            $this->getCourseMemberService()->removeStudent($course['id'], $user['id']);
        }
    }

    private function inviteReward($order)
    {
        $inviteSetting = $this->getSettingService()->get('invite', array());

        if (isset($inviteSetting['get_coupon_setting']) && $inviteSetting['get_coupon_setting'] == 1) {
            if ($order['pay_amount'] > 0) {
                $record = $this->getInviteRecordService()->getRecordByInvitedUserId($order['user_id']);

                if (!empty($record) && $record['inviteUserCardId'] == null) {
                    $inviteCoupon = $this->getCouponService()->generateInviteCoupon($record['inviteUserId'], 'pay');

                    if (!empty($inviteCoupon)) {
                        $this->getInviteRecordService()->addInviteRewardRecordToInvitedUser($order['user_id'], array('inviteUserCardId' => $inviteCoupon['id']));
                    }
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

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->getBiz()->service('Course:CourseService');
    }

    /**
     * @return MemberService
     */
    protected function getCourseMemberService()
    {
        return $this->getBiz()->service('Course:MemberService');
    }
}
