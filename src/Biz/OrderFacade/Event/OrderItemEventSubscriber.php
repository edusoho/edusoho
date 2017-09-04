<?php

namespace Biz\OrderFacade\Event;

use Biz\Coupon\Service\CouponService;
use Biz\OrderFacade\Product\Product;
use Biz\System\Service\SettingService;
use Biz\User\Service\InviteRecordService;
use Codeages\Biz\Framework\Event\Event;
use Codeages\PluginBundle\Event\EventSubscriber;

class OrderItemEventSubscriber extends EventSubscriber
{
    public static function getSubscribedEvents()
    {
        return array(
            'order.item.course.paid' => 'onCoursePaid',
            'order.item.classroom.paid' => 'onClassroomPaid',
        );
    }

    public function onCoursePaid(Event $event)
    {
        $orderItem = $event->getSubject();

        $product = $this->getProduct($orderItem['target_type']);

        $product->paidCallback($orderItem);
    }

    /**
     * @param $targetType
     * @return Product
     */
    private function getProduct($targetType)
    {
        $biz = $this->getBiz();

        $product = $biz['order.product.'.$targetType];

        return $product;
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
