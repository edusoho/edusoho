<?php

namespace Tests;


use Codeages\Biz\Order\Dao\OrderDao;
use Codeages\Biz\Order\Dao\OrderLogDao;
use Codeages\Biz\Order\Service\OrderService;
use Codeages\Biz\Order\Service\WorkflowService;

class OrderServiceTest extends IntegrationTestCase
{
    public function setUp()
    {
        parent::setUp();
        $currentUser = array(
            'id' => 1
        );
        $this->biz['user'] = $currentUser;
    }

    /**
     * @expectedException Codeages\Biz\Framework\Service\Exception\AccessDeniedException
     */
    public function testCreateOrderWithoutLogin()
    {
        $orderItems = $this->mockOrderItems();
        $order = $this->mockOrder();
        unset($this->biz['user']);
        $this->getWorkflowService()->start($order, $orderItems);
    }

    /**
     * @expectedException Codeages\Biz\Framework\Service\Exception\InvalidArgumentException
     */
    public function testCreateOrderWithoutTitle()
    {
        $orderItems = $this->mockOrderItems();
        unset($orderItems[0]['title']);
        $this->getWorkflowService()->start($this->mockOrder(), $orderItems);
    }

    /**
     * @expectedException Codeages\Biz\Framework\Service\Exception\InvalidArgumentException
     */
    public function testCreateOrderWithoutPriceAmount()
    {
        $orderItems = $this->mockOrderItems();
        unset($orderItems[0]['price_amount']);
        $this->getWorkflowService()->start($this->mockOrder(), $orderItems);
    }

    /**
     * @expectedException Codeages\Biz\Framework\Service\Exception\InvalidArgumentException
     */
    public function testCreateOrderWithoutTargetType()
    {
        $orderItems = $this->mockOrderItems();
        unset($orderItems[0]['target_type']);
        $this->getWorkflowService()->start($this->mockOrder(), $orderItems);
    }

    /**
     * @expectedException Codeages\Biz\Framework\Service\Exception\InvalidArgumentException
     */
    public function testCreateOrderWithoutTargetId()
    {
        $orderItems = $this->mockOrderItems();
        unset($orderItems[0]['target_id']);
        $this->getWorkflowService()->start($this->mockOrder(), $orderItems);
    }

    /**
     * @expectedException Codeages\Biz\Framework\Service\Exception\InvalidArgumentException
     */
    public function testCreateOrderWithoutUserId()
    {
        $orderItems = $this->mockOrderItems();
        $order = $this->mockOrder();
        unset($order['user_id']);
        $this->getWorkflowService()->start($order, $orderItems);
    }


    public function testCreateOrder()
    {
        $mockedOrderItems = $this->mockOrderItems();
        $mockOrder = $this->mockOrder();
        $order = $this->getWorkflowService()->start($mockOrder, $mockedOrderItems);
        $this->assertCreatedOrder($mockOrder, $mockedOrderItems, $order);
    }

    public function testCreateOrderWhenZeroPayAmount()
    {
        $mockedOrderItems = array(
            array(
                'title' => '人工智能神经网络',
                'detail' => '<div>独创的教学</div>',
                'price_amount' => 100,
                'target_id' => 1,
                'target_type' => 'course',
                'deducts' => array(
                    array(
                        'deduct_id' => 1,
                        'deduct_type' => 'discount',
                        'deduct_amount' => 20,
                        'detail' => '打折活动扣除10元'
                    ),
                    array(
                        'deduct_id' => 2,
                        'deduct_type' => 'coupon',
                        'deduct_amount' => 80,
                        'detail' => '使用优惠码扣除8元'
                    )
                )
            )
        );
        $mockOrder = $this->mockOrder();
        $order = $this->getWorkflowService()->start($mockOrder, $mockedOrderItems);
        $this->assertEquals('paid', $order['status']);
    }

    public function testPay()
    {
        $mockedOrderItems = $this->mockOrderItems();
        $order = $this->getWorkflowService()->start($this->mockOrder(), $mockedOrderItems);
        $data = array(
            'order_sn' => $order['sn'],
            'trade_sn' => '1234567',
            'pay_time' => time(),
            'payment_platform' => 'wechat'
        );
        $this->getWorkflowService()->paying($order['id']);
        $this->getWorkflowService()->paid($data);
        $order = $this->getOrderService()->getOrderBySn($order['sn']);
        $this->assertPaidOrder($data, $order);
    }

    /**
     * @expectedException Codeages\Biz\Framework\Service\Exception\AccessDeniedException
     */
    public function testCloseOrderWhenPaidStatus()
    {
        $mockedOrderItems = $this->mockOrderItems();
        $order = $this->getWorkflowService()->start($this->mockOrder(), $mockedOrderItems);
        $data = array(
            'order_sn' => $order['sn'],
            'trade_sn' => '1234567',
            'pay_time' => time()
        );
        $this->getWorkflowService()->paying($order['id']);
        $this->getWorkflowService()->paid($data);

        $this->getWorkflowService()->close($order['id']);
    }


    public function testCloseOrder()
    {
        $mockedOrderItems = $this->mockOrderItems();
        $order = $this->getWorkflowService()->start($this->mockOrder(), $mockedOrderItems);
        $order = $this->getWorkflowService()->close($order['id']);
        $this->assertEquals('closed', $order['status']);
        $this->assertNotEmpty($order['close_time']);

        $orderItems = $this->getOrderService()->findOrderItemsByOrderId($order['id']);
        foreach ($orderItems as $orderItem) {
            $this->assertEquals('closed', $orderItem['status']);
            $this->assertNotEmpty($orderItem['close_time']);
        }
    }

    public function testSearchOrderItems()
    {
        $mockedOrderItems = $this->mockOrderItems();
        $order = $this->getWorkflowService()->start($this->mockOrder(), $mockedOrderItems);
        $orderItems = $this->getOrderService()->searchOrderItems([], [], 0, PHP_INT_MAX);
        $this->assertEquals(2, count($orderItems));
    }

    public function testCountOrderItems()
    {
        $mockedOrderItems = $this->mockOrderItems();
        $order = $this->getWorkflowService()->start($this->mockOrder(), $mockedOrderItems);
        $count = $this->getOrderService()->countOrderItems([]);
        $this->assertEquals(2, $count);
    }

    protected function assertCreatedOrder($mockOrder, $mockedOrderItems, $order)
    {
        $this->assertNotEmpty($order);
        $this->assertNotEmpty($order['sn']);
        $this->assertEquals('created', $order['status']);
        $this->assertEquals('wap', $order['device']);
        $this->assertEquals($mockOrder['title'], $order['title']);
        $this->assertEquals($mockOrder['source'], $order['source']);
        $this->assertEquals($mockOrder['callback'], $order['callback']);
        $this->assertEquals($mockOrder['created_reason'], $order['created_reason']);
        $this->assertEquals($mockOrder['create_extra'], $order['create_extra']);
        $this->assertEquals($mockOrder['expired_refund_days'], $order['expired_refund_days']);
        $this->assertEquals($this->sumOrderPriceAmount($mockedOrderItems), $order['price_amount']);
        $this->assertEquals($this->sumOrderPayAmount($mockedOrderItems), $order['pay_amount']);
        $this->assertEquals($this->biz['user']['id'], $order['user_id']);
        $this->assertEquals($this->biz['user']['id'], $order['created_user_id']);

        $orderItems = $this->getOrderService()->findOrderItemsByOrderId($order['id']);
        $this->assertNotEmpty($orderItems);
        $this->assertEquals(count($mockedOrderItems), count($orderItems));

        for ($i = 0; $i < count($mockedOrderItems); $i++) {
            $item = $orderItems[$i];
            $mockedItem = $mockedOrderItems[$i];

            $this->assertNotEmpty($item['sn']);
            $this->assertEquals($order['id'], $item['order_id']);
            $this->assertEquals($mockedItem['title'], $item['title']);
            $this->assertEquals($mockedItem['detail'], $item['detail']);
            $this->assertEquals('created', $item['status']);
            $this->assertEquals($mockedItem['price_amount'], $item['price_amount']);
            $this->assertEquals($this->sumOrderItemPayAmount($mockedItem), $item['pay_amount']);
            $this->assertEquals($mockedItem['target_id'], $item['target_id']);
            $this->assertEquals($mockedItem['target_type'], $item['target_type']);
            $this->assertEquals($mockedItem['create_extra'], $item['create_extra']);
            $this->assertEquals($order['user_id'], $item['user_id']);
            $this->assertEquals($order['seller_id'], $item['seller_id']);

            $deducts = $this->getOrderService()->findOrderItemDeductsByItemId($item['id']);
            $this->assertEquals(count($mockedItem['deducts']), count($deducts));

            for ($j = 0; $j < count($deducts); $j++) {
                $deduct = $deducts[$j];
                $mockedDeduct = $mockedItem['deducts'][$j];

                $this->assertEquals($order['id'], $deduct['order_id']);
                $this->assertEquals($item['id'], $deduct['item_id']);
                $this->assertEquals($item['seller_id'], $deduct['seller_id']);
                $this->assertEquals($item['user_id'], $deduct['user_id']);
                $this->assertEquals($mockedDeduct['detail'], $deduct['detail']);
                $this->assertEquals($mockedDeduct['deduct_type'], $deduct['deduct_type']);
                $this->assertEquals($mockedDeduct['deduct_id'], $deduct['deduct_id']);
                $this->assertEquals($mockedDeduct['deduct_amount'], $deduct['deduct_amount']);
            }
        }
    }

    protected function assertPaidOrder($notifyData, $order)
    {
        $this->assertEquals('paid', $order['status']);
        $this->assertNotEmpty($order['pay_time']);
        $this->assertNotEmpty($order['trade_sn']);
        $this->assertEquals($notifyData['trade_sn'], $order['trade_sn']);
        $this->assertEquals($notifyData['pay_time'], $order['pay_time']);
        $this->assertEquals($order['pay_time'] + $order['expired_refund_days']*86400, $order['refund_deadline']);

        $orderItems = $this->getOrderService()->findOrderItemsByOrderId($order['id']);
        foreach ($orderItems as $orderItem) {
            $this->assertEquals('paid', $orderItem['status']);
            $this->assertEquals($order['pay_time'], $orderItem['pay_time']);
        }
    }

    protected function mockOrderItems()
    {
        return array(
            array(
                'title' => '人工智能神经网络',
                'detail' => '<div>独创的教学</div>',
                'price_amount' => 100,
                'target_id' => 1,
                'target_type' => 'course',
                'create_extra' => array(
                    'xxx' => 'xxx'
                ),
                'deducts' => array(
                    array(
                        'deduct_id' => 1,
                        'deduct_type' => 'discount',
                        'deduct_amount' => 10,
                        'detail' => '打折活动扣除10元'
                    ),
                    array(
                        'deduct_id' => 2,
                        'deduct_type' => 'coupon',
                        'deduct_amount' => 8,
                        'detail' => '使用优惠码扣除8元'
                    )
                )
            ),
            array(
                'title' => 'F1驾驶技术',
                'detail' => '<div>F1任丘人发生的发个</div>',
                'price_amount' => 110,
                'target_id' => 2,
                'target_type' => 'course',
                'create_extra' => array(
                    'xxx' => 'xxx'
                ),
                'deducts' => array(
                    array(
                        'deduct_id' => 3,
                        'deduct_type' => 'discount',
                        'deduct_amount' => 10,
                        'detail' => '打折活动扣除10元'
                    ),
                    array(
                        'deduct_id' => 5,
                        'deduct_type' => 'coupon',
                        'deduct_amount' => 4,
                        'detail' => '使用优惠码扣除4元'
                    )
                )
            )
        );
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\InvalidArgumentException
     */
    public function testAddOrderItemDeductWithErrorDeduct()
    {
        $deduct = array();

        $this->getOrderService()->addOrderItemDeduct($deduct);
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\AccessDeniedException
     */
    public function testAddOrderItemDeductWithErrorOrderStatus()
    {
        $mockedOrderItems = $this->mockOrderItems();
        $mockOrder = $this->mockOrder();
        $order = $this->getWorkflowService()->start($mockOrder, $mockedOrderItems);
        $order = $this->getOrderdao()->update($order['id'], array('status' => 'paid'));

        $deduct = array(
            'order_id' => $order['id'],
            'deduct_id' => 1,
            'deduct_type' => 'test',
            'deduct_amount' => 100,
            'user_id' => 1,
        );

        $this->getOrderService()->addOrderItemDeduct($deduct);
    }

    public function testAddOrderItemDeduct()
    {
        $mockedOrderItems = $this->mockOrderItems();
        $mockOrder = $this->mockOrder();
        $order = $this->getWorkflowService()->start($mockOrder, $mockedOrderItems);
        $deduct = array(
            'order_id' => $order['id'],
            'item_id' => 0,
            'deduct_id' => 1,
            'deduct_type' => 'test',
            'deduct_amount' => 100,
            'user_id' => 1,
        );

        $newDeduct = $this->getOrderService()->addOrderItemDeduct($deduct);
        $this->assertArraySubset($deduct, $newDeduct);

        $order = $this->getOrderdao()->get($order['id']);
        $this->assertEquals(78, $order['pay_amount']);
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\NotFoundException
     */
    public function testUpdateOrderItemDeductWithNotExist()
    {
        $this->getOrderService()->updateOrderItemDeduct(0, array());
    }

    public function testUpdateOrderItemDeduct()
    {
        $mockedOrderItems = $this->mockOrderItems();
        $mockOrder = $this->mockOrder();
        $order = $this->getWorkflowService()->start($mockOrder, $mockedOrderItems);
        $deduct = array(
            'order_id' => $order['id'],
            'item_id' => 0,
            'deduct_id' => 1,
            'deduct_type' => 'test',
            'deduct_amount' => 100,
            'user_id' => 1,
        );

        $newDeduct = $this->getOrderService()->addOrderItemDeduct($deduct);
        $newDeduct = $this->getOrderService()->updateOrderItemDeduct($newDeduct['id'], array('deduct_amount' => 10));
        $this->assertEquals(10 , $newDeduct['deduct_amount']);

        $order = $this->getOrderdao()->get($order['id']);
        $this->assertEquals(168, $order['pay_amount']);
    }

    public function testAdjustPrice()
    {
        $mockedOrderItems = $this->mockOrderItems();
        $mockOrder = $this->mockOrder();
        $order = $this->getWorkflowService()->start($mockOrder, $mockedOrderItems);
        $adjustDeduct = $this->getWorkflowService()->adjustPrice($order['id'], 158);

        $this->assertEquals(20, $adjustDeduct['deduct_amount']);
        $this->assertArrayHasKey('order', $adjustDeduct);

        $orderLogs = $this->getOrderLogDao()->search(array('status' => 'order.adjust_price'), array(), 0, 1);
        $this->assertNotNull($orderLogs);
    }

    public function testAdjustPriceSecondTime()
    {
        $mockedOrderItems = $this->mockOrderItems();
        $mockOrder = $this->mockOrder();
        $order = $this->getWorkflowService()->start($mockOrder, $mockedOrderItems);

        $adjustDeduct = $this->getWorkflowService()->adjustPrice($order['id'], 158);
        $this->assertEquals(20, $adjustDeduct['deduct_amount']);

        $adjustDeduct = $this->getWorkflowService()->adjustPrice($order['id'], 100);
        $this->assertEquals(78, $adjustDeduct['deduct_amount']);
    }

    protected function sumOrderItemPayAmount($item)
    {
        $priceAmount = $item['price_amount'];
        foreach ($item['deducts'] as $deduct) {
            $priceAmount = $priceAmount - $deduct['deduct_amount'];
        }
        return $priceAmount;
    }

    protected function mockOrder()
    {
        return array(
            'title' => '购买商品',
            'callback' => array('url'=>'http://try6.edusoho.cn/'),
            'source' => 'custom',
            'price_type' => 'coin',
            'user_id' => $this->biz['user']['id'],
            'created_reason' => '购买',
            'create_extra' => array(
                'xxx' => 'xxx'
            ),
            'device' => 'wap',
            'expired_refund_days' => 5
        );
    }

    protected function sumOrderPriceAmount($items)
    {
        $price = 0;
        foreach ($items as $item) {
            $price = $price + $item['price_amount'];
        }
        return $price;
    }

    protected function sumOrderPayAmount($items)
    {
        $priceAmount = $this->sumOrderPriceAmount($items);
        foreach ($items as $item) {
            foreach ($item['deducts'] as $deduct) {
                $priceAmount = $priceAmount - $deduct['deduct_amount'];
            }
        }
        if ($priceAmount < 0) {
            $priceAmount = 0;
        }
        return $priceAmount;
    }

    /**
     * @return OrderService
     */
    protected function getOrderService()
    {
        return $this->biz->service('Order:OrderService');
    }

    /**
     * @return WorkflowService
     */
    protected function getWorkflowService()
    {
        return $this->biz->service('Order:WorkflowService');
    }

    protected function getOrderItemRefundDao()
    {
        return $this->biz->dao('Order:OrderItemRefundDao');
    }

    /**
     * @return OrderDao
     */
    protected function getOrderdao()
    {
        return $this->biz->dao('Order:OrderDao');
    }

    /**
     * @return OrderLogDao
     */
    protected function getOrderLogDao()
    {
        return $this->biz->dao('Order:OrderLogDao');
    }

}