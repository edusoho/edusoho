<?php
namespace Topxia\Service\Coupon\Event;

use Topxia\Service\Common\ServiceEvent;
use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CouponBatchEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'coupon.use' => 'onCouponUse'
        );
    }

    public function onCouponUse(ServiceEvent $event)
    {
        $coupon['batchId'] = $event->getSubject();
        $usedCount         = $event->getArgument('usedNum');
        $allDiscount       = $event->getArgument('money');

        $this->getCouponBatchService()->updateBatch($coupon['batchId'], array('usedNum' => $usedCount, 'money' => $allDiscount));
    }

    private function getCouponBatchService()
    {
        return ServiceKernel::instance()->createService('Coupon:Coupon.CouponBatchService');
    }
}
