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

        $container = $kernel->getContainer();
        /*$session       = $container->get('request')->getSession();
        $refererLogIds = unserialize($session->get('refererLogIds'));*/
        $refererLogIds = unserialize($container->get('request')->cookies->get('refererLogIds'));

        $order = $event->getSubject();

        if (empty($refererLogIds)) {
            return false;
        }

        foreach ($refererLogIds as $key => $refererLogId) {
            $refererLog = $this->getRefererLogService()->getRefererLogById($refererLogId);
            $fields     = array(
                'refererLogId'     => $refererLogId,
                'orderId'          => $order['id'],
                'sourceTargetId'   => $refererLog['targetId'],
                'sourceTargetType' => $refererLog['targetType'],
                'targetType'       => $order['targetType'],
                'targetId'         => $order['targetId'],
                'createdUserId'    => $order['userId']
            );

            $this->getOrderRefererLogService()->addOrderRefererLog($fields);

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
