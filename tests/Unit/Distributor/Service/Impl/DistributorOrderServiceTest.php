<?php

namespace Tests\Unit\Distributor\Service\Impl;

use Biz\BaseTestCase;
use AppBundle\Common\ReflectionUtils;

class DistributorOrderServiceTest extends BaseTestCase
{
    public function testGetSendType()
    {
        $this->assertEquals('order', $this->getDistributorOrderService()->getSendType());
    }

    public function testGetJobType()
    {
        $jobType = ReflectionUtils::invokeMethod($this->getDistributorOrderService(), 'getJobType', array());
        $this->assertEquals('Order', $jobType);
    }

    public function testFindJobData()
    {
        $distributorJobDataDao = $this->mockBiz(
            'Distributor:DistributorJobDataDao',
            array(
                array(
                    'functionName' => 'search',
                    'withParams' => array(
                        array(
                            'jobType' => 'Order',
                            'statusArr' => array('pending', 'error'),
                        ),
                        array(),
                        0,
                        100,
                    ),
                    'returnValue' => 'abc',
                ),
            )
        );

        $result = $this->getDistributorOrderService()->findJobData();

        $this->assertEquals('abc', $result);
        $distributorJobDataDao->shouldHaveReceived('search')->times(1);
    }

    public function testCreateJobData()
    {
        $orderService = $this->mockBiz(
            'Order:OrderService',
            array(
                array(
                    'functionName' => 'findOrderItemsByOrderId',
                    'withParams' => array(123),
                    'returnValue' => array(array(
                        'target_type' => 'course',
                        'target_id' => 1,
                    )),
                ),
                array(
                    'functionName' => 'findOrderItemDeductsByOrderId',
                    'withParams' => array(123),
                    'returnValue' => array(
                        array(
                            'deduct_type' => 'discount',
                            'deduct_amount' => 1100,
                            'deduct_type_name' => '折扣',
                        ),
                    ),
                ),
            )
        );

        $distributorJobDataDao = $this->mockBiz(
            'Distributor:DistributorJobDataDao',
            array(
                array(
                    'functionName' => 'create',
                    'withParams' => array(array(
                        'data' => array(
                            'user_source_id' => 2,
                            'source_id' => 123,
                            'product_type' => 'course',
                            'product_id' => 1,
                            'title' => 'course title',
                            'sn' => 'course sn',
                            'created_time' => 123456,
                            'payment_time' => 123123,
                            'refund_expiry_day' => 1,
                            'refund_deadline' => 32222,
                            'price' => 100,
                            'pay_amount' => 90,
                            'deduction' => array(
                                array(
                                    'type' => 'discount',
                                    'detail' => '折扣',
                                    'amount' => 1100,
                                ),
                            ),
                            'status' => 'finished',
                        ),
                        'jobType' => 'Order',
                        'status' => 'pending',
                    )),
                    'returnValue' => array('abc'),
                ),
            )
        );

        $result = $this->getDistributorOrderService()->createJobData(
            array(
                'id' => 123,
                'user_id' => 2,
                'title' => 'course title',
                'sn' => 'course sn',
                'created_time' => 123456,
                'pay_time' => 123123,
                'expired_refund_days' => 1,
                'refund_deadline' => 32222,
                'price_amount' => 100,
                'pay_amount' => 90,
                'status' => 'finished',
            )
        );

        $this->assertNull($result);
        $distributorJobDataDao->shouldHaveReceived('create')->times(1);
        $orderService->shouldHaveReceived('findOrderItemsByOrderId')->times(1);
        $orderService->shouldHaveReceived('findOrderItemDeductsByOrderId')->times(1);
    }

    public function testBatchUpdateStatus()
    {
        $distributorJobDataDao = $this->mockBiz(
            'Distributor:DistributorJobDataDao',
            array(
                array(
                    'functionName' => 'batchUpdate',
                    'withParams' => array(
                        array(1, 2),
                        array(1 => array('status' => 'finished'), 2 => array('status' => 'finished')),
                        'id',
                    ),
                ),
            )
        );
        $result = $this->getDistributorOrderService()->batchUpdateStatus(
            array(
                array('id' => 1),
                array('id' => 2),
            ),
            'finished'
        );
        $this->assertNull($result);
        $distributorJobDataDao->shouldHaveReceived('batchUpdate');
    }

    private function getDistributorOrderService()
    {
        return $this->createService('Distributor:DistributorOrderService');
    }
}
