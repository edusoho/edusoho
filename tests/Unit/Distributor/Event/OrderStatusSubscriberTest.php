<?php

namespace Tests\Unit\Distributor\Event;

use Biz\BaseTestCase;
use Biz\Distributor\Event\OrderStatusSubscriber;
use Codeages\Biz\Framework\Event\Event;

class OrderStatusSubscriberTest extends BaseTestCase
{
    public function testGetSubscribedEvents()
    {
        $subscriber = new OrderStatusSubscriber($this->getBiz());
        $this->assertArrayEquals(
            array(
                'order.finished' => 'onOrderChangeStatus',
                'order.success' => 'onOrderChangeStatus',
                'order.refunded' => 'onOrderChangeStatus',
            ), $subscriber->getSubscribedEvents()
        );
    }

    public function testOnOrderChangeStatus()
    {
        $orderService = $this->mockBiz(
            'Order:OrderService',
            array(
                array(
                    'functionName' => 'findOrdersByIds',
                    'withParams' => array(array(123)),
                    'returnValue' => array(array('user_id' => 12322)),
                ),
            )
        );

        $userService = $this->mockBiz(
            'User:UserService',
            array(
                array(
                    'functionName' => 'findUsersByIds',
                    'withParams' => array(array(12322)),
                    'returnValue' => array('12322' => array('name' => 'order name', 'type' => 'distributor')),
                ),
            )
        );

        $distributorOrderService = $this->mockBiz(
            'Distributor:DistributorOrderService',
            array(
                array(
                    'functionName' => 'batchCreateJobData',
                    'withParams' => array(array(array('user_id' => 12322))),
                ),
            )
        );
        $event = new Event(
            array(
                'items' => array(array(
                    'order_id' => 123,
                )),
                'user_id' => 12322,
            )
        );

        $subscriber = new OrderStatusSubscriber($this->getBiz());
        $result = $subscriber->onOrderChangeStatus($event);

        $this->assertNull($result);
        $orderService->shouldHaveReceived('findOrdersByIds')->times(1);
        $userService->shouldHaveReceived('findUsersByIds')->times(1);
        $distributorOrderService->shouldHaveReceived('batchCreateJobData')->times(1);
    }
}
