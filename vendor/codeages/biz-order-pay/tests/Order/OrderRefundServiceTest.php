<?php

namespace Tests;

use Codeages\Biz\Framework\Util\ArrayToolkit;
use Codeages\Biz\Order\Service\WorkflowService;

class OrderRefundServiceTest extends IntegrationTestCase
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
     * @expectedException \Codeages\Biz\Framework\Service\Exception\AccessDeniedException
     */
    public function testFinishOrderRefundWithoutCurrentUser()
    {
        $orderRefund = $this->mockOrderRefund();
        unset($this->biz['user']);
        $this->getWorkflowService()->adoptRefund($orderRefund['id'], array('deal_reason' => '通过'));
        $this->getWorkflowService()->setRefunded($orderRefund['id']);
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\AccessDeniedException
     */
    public function testRefuseOrderRefundWithoutCurrentUser()
    {
        $orderRefund = $this->mockOrderRefund();
        unset($this->biz['user']);
        $this->getWorkflowService()->refuseRefund($orderRefund['id'], array('deal_reason' => '拒绝'));
    }

    public function testFinishOrderRefund()
    {
        $orderRefund = $this->mockOrderRefund();
        $this->getWorkflowService()->adoptRefund($orderRefund['id'], array('deal_reason' => '通过'));
        $orderRefund = $this->getWorkflowService()->setRefunded($orderRefund['id']);
        $this->assertEquals('refunded', $orderRefund['status']);
        $this->assertNotEmpty($orderRefund['deal_time']);
        $this->assertNotEmpty($orderRefund['deal_reason']);
        $this->assertEquals($this->biz['user']['id'], $orderRefund['deal_user_id']);
    }

    public function testSetRefusedOrderRefund()
    {
        $orderRefund = $this->mockOrderRefund();
        $orderRefund = $this->getWorkflowService()->refuseRefund($orderRefund['id'], array('deal_reason' => '拒绝'));
        $this->assertEquals('refused', $orderRefund['status']);
        $this->assertNotEmpty($orderRefund['deal_time']);
        $this->assertNotEmpty($orderRefund['deal_reason']);
        $this->assertEquals($this->biz['user']['id'], $orderRefund['deal_user_id']);
    }

    public function testCancelOrderRefund()
    {
        $orderRefund = $this->mockOrderRefund();
        $orderRefund = $this->getWorkflowService()->cancelRefund($orderRefund['id']);
        $this->assertEquals('cancel', $orderRefund['status']);
    }

    public function testSetRefundedOrderItemRefunds()
    {
        $orderRefund = $this->mockOrderItemRefunds();
        $this->getWorkflowService()->adoptRefund($orderRefund['id'], array('deal_reason' => '对该课程不感兴趣'));
        $orderRefund = $this->getWorkflowService()->setRefunded($orderRefund['id']);
        $this->assertEquals('refunded', $orderRefund['status']);
        $this->assertNotEmpty($orderRefund['deal_time']);
        $this->assertNotEmpty($orderRefund['deal_reason']);
        $this->assertEquals($this->biz['user']['id'], $orderRefund['deal_user_id']);

        $orderRefundItems = $this->getOrderItemRefundDao()->findByOrderRefundId($orderRefund['id']);
        $this->assertNotEmpty($orderRefundItems);
        foreach ($orderRefundItems as $orderRefundItem) {
            $this->assertEquals('refunded', $orderRefundItem['status']);
        }
    }

    /**
     * @return mixed
     */
    protected function mockOrderRefund()
    {
        $mockedOrderItems = $this->mockOrderItems();
        $order = $this->getWorkflowService()->start($this->mockOrder(), $mockedOrderItems);
        $data = array(
            'order_sn' => $order['sn'],
            'trade_sn' => '',
            'pay_time' => time()
        );

        $this->getWorkflowService()->paying($order['id']);
        $this->getWorkflowService()->paid($data);
        $this->getWorkflowService()->finish($order['id'], array());
        $orderRefund = $this->getWorkflowService()->applyOrderRefund($order['id'], array('reason' => '对该课程不感兴趣'));
        $this->assertNotEmpty($orderRefund);
        $this->assertNotEmpty($orderRefund['sn']);
        $this->assertNotEmpty($orderRefund['created_user_id']);
        $this->assertEquals($order['id'], $orderRefund['order_id']);
        $this->assertEquals(0, $orderRefund['order_item_id']);
        $this->assertEquals($this->biz['user']['id'], $orderRefund['user_id']);
        $this->assertEquals($order['pay_amount'], $orderRefund['amount']);
        $this->assertEquals('auditing', $orderRefund['status']);
        return $orderRefund;
    }

    /**
     * @return mixed
     */
    protected function mockOrderItemRefunds()
    {
        $mockedOrderItems = $this->mockOrderItems();
        $order = $this->getWorkflowService()->start($this->mockOrder(), $mockedOrderItems);
        $data = array(
            'order_sn' => $order['sn'],
            'trade_sn' => '',
            'pay_time' => time()
        );
        $this->getWorkflowService()->paying($order['id']);
        $this->getWorkflowService()->paid($data);
        $this->getWorkflowService()->finish($order['id'], array());
        $orderItemIds = ArrayToolkit::column($order['items'], 'id');
        $orderRefund = $this->getWorkflowService()->applyOrderItemsRefund($order['id'], $orderItemIds, array('reason' => '对该课程不感兴趣'));

        $this->assertNotEmpty($orderRefund);
        $this->assertNotEmpty($orderRefund['sn']);
        $this->assertNotEmpty($orderRefund['created_user_id']);
        $this->assertEquals($order['id'], $orderRefund['order_id']);
        $this->assertEquals(0, $orderRefund['order_item_id']);
        $this->assertEquals($this->biz['user']['id'], $orderRefund['user_id']);
        $this->assertEquals($order['pay_amount'], $orderRefund['amount']);
        $this->assertEquals('auditing', $orderRefund['status']);
        return $orderRefund;
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

    protected function mockOrder()
    {
        return array(
            'title' => '购买商品',
            'callback' => array('url' => 'http://try6.edusoho.cn/'),
            'source' => 'custom',
            'price_type' => 'coin',
            'user_id' => $this->biz['user']['id'],
            'created_reason' => '购买'
        );
    }

    protected function getOrderItemRefundDao()
    {
        return $this->biz->dao('Order:OrderItemRefundDao');
    }

    /**
     * @return WorkflowService
     */
    protected function getWorkflowService()
    {
        return $this->biz->service('Order:WorkflowService');
    }

    protected function getOrderService()
    {
        return $this->biz->service('Order:OrderService');
    }

    protected function getOrderRefundService()
    {
        return $this->biz->service('Order:OrderRefundService');
    }
}