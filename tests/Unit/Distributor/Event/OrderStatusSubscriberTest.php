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

    /**
     * 用户拉新订单上报测试
     */
    public function testOnOrderChangeStatusWithUserRegister()
    {
        $orderService = $this->mockBiz(
            'Order:OrderService',
            array(
                array(
                    'functionName' => 'findOrdersByIds',
                    'withParams' => array(array(123)),
                    'returnValue' => array(array('user_id' => 12322, 'id' => 123)),
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
                    'withParams' => array(array(array('user_id' => 12322, 'id' => 123))),
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

    /**
     * 课程分销订单上报测试
     */
    public function testOnOrderChangeStatusWithCourseProduct()
    {
        $orderService = $this->mockBiz(
            'Order:OrderService',
            array(
                array(
                    'functionName' => 'findOrdersByIds',
                    'withParams' => array(array(123)),
                    'returnValue' => array(array('user_id' => 12322, 'id' => 123)),
                ),
            )
        );

        $userService = $this->mockBiz(
            'User:UserService',
            array(
                array(
                    'functionName' => 'findUsersByIds',
                    'withParams' => array(array(12322)),
                    'returnValue' => array('12322' => array('name' => 'order name')),
                ),
            )
        );

        $distributorOrderService = $this->mockBiz(
            'Distributor:DistributorOrderService',
            array(
                array(
                    'functionName' => 'batchCreateJobData',
                    'withParams' => array(),
                ),
            )
        );

        $distributorCourseOrderService = $this->mockBiz(
            'Distributor:DistributorCourseOrderService',
            array(
                array(
                    'functionName' => 'batchCreateJobData',
                    'withParams' => array(array(array('user_id' => 12322, 'id' => 123))),
                ),
            )
        );

        $token = 'courseOrder:9:333:123:1524324352:c9a10dc1737f63a43d2ca6d155155999:51imxY0F11R2ZHWK1TpLiYk9bo4=';
        $event = new Event(
            array(
                'items' => array(array(
                    'order_id' => 123,
                    'create_extra' => array(
                        'distributorToken' => $token,
                    ),
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
        $distributorCourseOrderService->shouldHaveReceived('batchCreateJobData')->times(1);
    }
}
