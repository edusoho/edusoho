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
            'order.service.paid'    => 'onOrderPaid',
            'order.service.created' => 'onOrderCreated'
        );
    }

    public function onOrderCreated(ServiceEvent $event)
    {
        global $kernel;

        if(empty($kernel)){
            return false;
        }

        $uv = $kernel->getContainer()->get('request')->cookies->get('uv');

        $token = $this->getRefererLogService()->getOrderRefererByUv($uv);

        if (empty($token)) {
            return false;
        }
        $order    = $event->getSubject();
        $orderIds = explode("|", trim($token['orderIds'], "|"));
        array_push($orderIds, $order['id']);

        $token['orderIds'] = '|'.implode($orderIds, "|").'|';

        $this->getRefererLogService()->updateOrderReferer($token['id'], $token);
    }

    public function onOrderPaid(ServiceEvent $event)
    {
        $order = $event->getSubject();

        $token = $this->getRefererLogService()->getOrderRefererLikeByOrderId($order['id']);

        if (empty($token) || $order['totalPrice'] == 0) {
            return false;
        }

        $refererOrderIds = array_values($token['data']);

        $refererLogs = $this->getRefererLogService()->searchRefererLogs(
            array('ids' => $refererOrderIds),
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
