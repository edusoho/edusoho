<?php
namespace Topxia\Service\RefererLog\Event;

use Topxia\Service\Common\ServiceEvent;
use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class OrderRefererLogEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'order.service.paid' => 'onOrderPaid'
        );
    }

    public function onOrderPaid(ServiceEvent $event)
    {
        global $kernel;

        $container    = $kernel->getContainer();
        $session      = $container->get('request')->getSession();
        $refererLogId = $session->get('refererLogId');

        $order = $event->getSubject();

        if ($refererLogId) {
            $fields = array(
                'refererLogId'  => $refererLogId,
                'orderId'       => $order['id'],
                'targetType'    => $order['targetType'],
                'targetId'      => $order['targetId'],
                'createdUserId' => $order['userId']
            );

            $refererLog = $this->getOrderRefererLogService()->addOrderRefererLog($fields);

            $this->getRefererLogService()->waveRefererLog($refererLogId, 'orderCount', 1);
        }
    }

    protected function getOrderRefererLogService()
    {
        return ServiceKernel::instance()->createService('RefererLog.OrderRefererLogService');
    }

    protected function getRefererLogService()
    {
        return ServiceKernel::instance()->createService('RefererLog.RefererLogService');
    }
}
