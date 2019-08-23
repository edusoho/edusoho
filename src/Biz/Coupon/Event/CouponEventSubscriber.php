<?php

namespace Biz\Coupon\Event;

use Biz\Coupon\Service\CouponService;
use Biz\Order\Service\OrderService;
use Codeages\Biz\Framework\Event\Event;
use Codeages\PluginBundle\Event\EventSubscriber;
use Biz\Coupon\Service\CouponBatchService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use AppBundle\Common\MathToolkit;

class CouponEventSubscriber extends EventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'coupon.use' => 'onCouponUse',
        );
    }

    public function onCouponUse(Event $event)
    {
        $coupon = $event->getSubject();

        if (empty($coupon['batchId'])) {
            return;
        }

        $usedCount = $this->getCouponService()->searchCouponsCount(
            array('status' => 'used', 'batchId' => $coupon['batchId'])
        );
        $allDiscount = $this->getCouponBatchService()->sumDeductAmountByBatchId($coupon['batchId']);

        $this->getCouponBatchService()->updateBatch(
            $coupon['batchId'],
            array('usedNum' => $usedCount, 'money' => MathToolkit::simple($allDiscount, 0.01))
        );
    }

    /**
     * @return CouponService
     */
    private function getCouponService()
    {
        return $this->getBiz()->service('Coupon:CouponService');
    }

    /**
     * @return OrderService
     */
    private function getOrderService()
    {
        return $this->getBiz()->service('Order:OrderService');
    }

    /**
     * @return CouponBatchService
     */
    private function getCouponBatchService()
    {
        return $this->getBiz()->service('Coupon:CouponBatchService');
    }
}
