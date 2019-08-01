<?php

namespace Tests\Unit\RefererLog\Event;

use Biz\BaseTestCase;
use Biz\RefererLog\Event\OrderRefererLogEventSubscriber;
use Codeages\Biz\Framework\Event\Event;

class OrderRefererLogEventSubscriberTest extends BaseTestCase
{
    public function testGetSubscribedEvents()
    {
        $this->assertEquals(array(
            'order.paid' => 'onOrderPaid',
            'order.created' => 'onOrderCreated',
        ), OrderRefererLogEventSubscriber::getSubscribedEvents());
    }

    public function testOnOrderCreatedWithoutToken()
    {
        $event = new Event(array());
        $eventSubscriber = new OrderRefererLogEventSubscriber($this->biz);

        $result = $eventSubscriber->onOrderCreated($event);
        $this->assertFalse($result);
    }

    public function testOnOrderCreated()
    {
        $service = $this->mockBiz('RefererLog:RefererLogService', array(
            array(
                'functionName' => 'getOrderRefererByUv',
                'returnValue' => array(
                    'id' => 1,
                    'orderIds' => '|1|',
                ),
            ),
            array(
                'functionName' => 'updateOrderReferer',
            ),
        ));

        $event = new Event(array('id' => 2));
        $eventSubscriber = new OrderRefererLogEventSubscriber($this->biz);

        $result = $eventSubscriber->onOrderCreated($event);
        $this->assertNull($result);
        $service->shouldHaveReceived('updateOrderReferer')->times(1);
    }

    public function testOnOrderPaidWithoutToken()
    {
        $orderService = $this->mockBiz('Order:OrderService', array(
            array(
                'functionName' => 'findOrderItemsByOrderId',
                'withParams' => array(2),
                'returnValue' => array(
                    array(
                        'target_type' => 'course',
                        'target_id' => 1,
                    ),
                ),
            ),
        ));
        $refererLogService = $this->mockBiz('RefererLog:RefererLogService', array(
            array(
                'functionName' => 'getOrderRefererLikeByOrderId',
                'withParams' => array(2),
                'returnValue' => array(),
            ),
        ));
        $event = new Event(array('id' => 2, 'price_amount' => 0));
        $eventSubscriber = new OrderRefererLogEventSubscriber($this->biz);

        $result = $eventSubscriber->onOrderPaid($event);

        $orderService->shouldHaveReceived('findOrderItemsByOrderId')->times(1);
        $refererLogService->shouldHaveReceived('getOrderRefererLikeByOrderId')->times(1);
        $this->assertFalse($result);
    }

    public function testOnOrderPadiWithoutRefererLog()
    {
        $orderService = $this->mockBiz('Order:OrderService', array(
            array(
                'functionName' => 'findOrderItemsByOrderId',
                'withParams' => array(2),
                'returnValue' => array(
                    array(
                        'target_type' => 'course',
                        'target_id' => 1,
                    ),
                ),
            ),
        ));
        $refererLogService = $this->mockBiz('RefererLog:RefererLogService', array(
            array(
                'functionName' => 'getOrderRefererLikeByOrderId',
                'withParams' => array(2),
                'returnValue' => array(
                    'data' => array(
                        'course_1' => '123',
                    ),
                ),
            ),
            array(
                'functionName' => 'searchRefererLogs',
                'returnValue' => array(),
            ),
        ));
        $event = new Event(array('id' => 2, 'price_amount' => 1));
        $eventSubscriber = new OrderRefererLogEventSubscriber($this->biz);

        $result = $eventSubscriber->onOrderPaid($event);

        $orderService->shouldHaveReceived('findOrderItemsByOrderId')->times(1);
        $refererLogService->shouldHaveReceived('getOrderRefererLikeByOrderId')->times(1);
        $refererLogService->shouldHaveReceived('searchRefererLogs')->times(1);
        $this->assertFalse($result);
    }

    public function testOnOrderPaid()
    {
        $orderService = $this->mockBiz('Order:OrderService', array(
            array(
                'functionName' => 'findOrderItemsByOrderId',
                'withParams' => array(2),
                'returnValue' => array(
                    array(
                        'target_type' => 'course',
                        'target_id' => 1,
                    ),
                ),
            ),
        ));
        $refererLogService = $this->mockBiz('RefererLog:RefererLogService', array(
            array(
                'functionName' => 'getOrderRefererLikeByOrderId',
                'withParams' => array(2),
                'returnValue' => array(
                    'data' => array(
                        'course_1' => '123',
                    ),
                ),
            ),
            array(
                'functionName' => 'searchRefererLogs',
                'returnValue' => array(
                    array(
                        'id' => 1,
                        'targetType' => 'course',
                        'targetId' => 1,
                    ),
                    array(
                        'id' => 2,
                        'targetType' => 'course',
                        'targetId' => 2,
                    ),
                ),
            ),
            array(
                'functionName' => 'waveRefererLog',
            ),
        ));
        $orderRefererService = $this->mockBiz('RefererLog:OrderRefererLogService', array(
            array(
                'functionName' => 'addOrderRefererLog',
            ),
        ));
        $event = new Event(array('id' => 2, 'price_amount' => 1, 'user_id' => 1));
        $eventSubscriber = new OrderRefererLogEventSubscriber($this->biz);

        $result = $eventSubscriber->onOrderPaid($event);

        $orderService->shouldHaveReceived('findOrderItemsByOrderId')->times(1);
        $refererLogService->shouldHaveReceived('getOrderRefererLikeByOrderId')->times(1);
        $refererLogService->shouldHaveReceived('searchRefererLogs')->times(1);
        $refererLogService->shouldHaveReceived('waveRefererLog')->times(2);
        $orderRefererService->shouldHaveReceived('addOrderRefererLog')->times(2);
    }
}
