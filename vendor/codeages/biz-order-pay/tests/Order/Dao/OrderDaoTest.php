<?php

namespace Tests;

use Codeages\Biz\Order\Dao\OrderDao;

class OrderDaoTest extends IntegrationTestCase
{
    public function testQueryWithItemConditions()
    {
        $this->getOrderDao()->create(
            array(
                'id' => 100,
                'title' => 'order_title',
                'sn' => 'order_sn',
                'price_amount' => 0,
                'price_type' => 'abc',
                'pay_amount' => 0,
                'user_id' => 1,
            )
        );

        $this->getOrderItemDao()->create(
            array(
                'id' => 10101,
                'order_id' => 100,
                'title' => 'order_item_name',
                'sn' => 'order_item_sn',
                'price_amount' => 0,
                'pay_amount' => 0,
                'user_id' => 1,
                'target_id' => 1,
                'target_type' => 'course',
            )
        );

        $orderInfos = $this->getOrderdao()->queryWithItemConditions(
            array('order_item_title' => 'rder_item_n'),
            array('created_time' => 'desc'),
            0,
            2
        );

        $orderCount = $this->getOrderDao()->queryCountWithItemConditions(
            array('order_item_title' => 'rder_item_n')
        );

        $this->assertEquals('order_title', $orderInfos[0]['title']);
        $this->assertEquals(1, $orderCount);
        $this->assertEquals(1, count($orderInfos));
    }

    /**
     * @return OrderDao
     */
    protected function getOrderDao()
    {
        return $this->biz->dao('Order:OrderDao');
    }

    protected function getOrderItemDao()
    {
        return $this->biz->dao('Order:OrderItemDao');
    }
}
