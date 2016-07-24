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

        $refererLogToken = unserialize($container->get('request')->cookies->get('refererLogToken'));

        $order = $event->getSubject();

        if (empty($refererLogToken) || $order['totalPrice'] == 0) {
            return false;
        }

        $refererLogs = $this->getRefererLogService()->searchRefererLogs(
            array('ids' => array_values($refererLogToken)),
            array('createdTime', 'DESC'),
            0, PHP_INT_MAX
        );

        if (!$refererLogs) {
            return false;
        }

        foreach ($refererLogs as $key => $refererLog) {
            $fields = array(
                'refererLogId'     => $refererLog['id'],
                'orderId'          => $order['id'],
                'sourceTargetId'   => $refererLog['targetId'],
                'sourceTargetType' => $refererLog['targetType'],
                'targetType'       => $order['targetType'],
                'targetId'         => $order['targetId'],
                'createdUserId'    => $order['userId']
            );

            $this->getOrderRefererLogService()->addOrderRefererLog($fields);

            $this->getRefererLogService()->waveRefererLog($refererLog['id'], 'orderCount', 1);
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
