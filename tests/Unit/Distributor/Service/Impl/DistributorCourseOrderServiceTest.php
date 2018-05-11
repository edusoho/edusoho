<?php

namespace Tests\Unit\Distributor\Service\Impl;

use Biz\BaseTestCase;
use AppBundle\Common\ReflectionUtils;
use AppBundle\Common\TimeMachine;

class DistributorCourseOrderServiceTest extends BaseTestCase
{
    public function testGetSendType()
    {
        $this->assertEquals('order.refunded', $this->getDistributorCourseOrderService()->getSendType(array('status' => 'refunded')));
    }

    public function testGetRoutingName()
    {
        $this->assertEquals('course_show', $this->getDistributorCourseOrderService()->getRoutingName());
    }

    public function testGetRoutingParams()
    {
        $token = 'courseOrder:9:333:123:1524313483:8a4323be2ae4d5b7fa1bec53c43b203c:Sgts-yLzLy5PH5c2NJ_s2Xdd_4U=';
        $this->assertEquals(array('id' => '9'), $this->getDistributorCourseOrderService()->getRoutingParams($token));
    }

    public function testDecodeToken()
    {
        $settingService = $this->mockBiz(
            'System:SettingService',
            array(
                array(
                    'functionName' => 'get',
                    'withParams' => array('storage', array()),
                    'returnValue' => array(
                        'cloud_access_key' => 'abc',
                        'cloud_secret_key' => 'efg',
                    ),
                ),
                array(
                    'functionName' => 'get',
                    'withParams' => array('developer', array()),
                    'returnValue' => array(
                    ),
                ),
            )
        );

        $token = 'courseOrder:9:123:333:1524324352:c9a10dc1737f63a43d2ca6d155155999:2DQ1xlkUFVceNkn_QLOvf3acM8w=';
        $splitedTokens = $this->getDistributorCourseOrderService()->decodeToken($token);

        $this->assertTrue($splitedTokens['valid']);
        $this->assertEquals('courseOrder', $splitedTokens['type']);
        $this->assertEquals('9', $splitedTokens['product_id']);
    }

    public function testGenerateMockedToken()
    {
        TimeMachine::setMockedTime(1524324352);
        $settingService = $this->mockBiz(
            'System:SettingService',
            array(
                array(
                    'functionName' => 'get',
                    'withParams' => array('storage', array()),
                    'returnValue' => array(
                        'cloud_access_key' => 'abc',
                        'cloud_secret_key' => 'efg',
                    ),
                ),
            )
        );

        $token = $this->getDistributorCourseOrderService()->generateMockedToken(array('courseId' => '9'));

        $this->assertEquals(
            'courseOrder:9:123:333:1524324352:c9a10dc1737f63a43d2ca6d155155999:2DQ1xlkUFVceNkn_QLOvf3acM8w=',
            $token
        );
        $settingService->shouldHaveReceived('get');
    }

    public function testGetJobType()
    {
        $jobType = ReflectionUtils::invokeMethod($this->getDistributorCourseOrderService(), 'getJobType', array());
        $this->assertEquals('CourseOrder', $jobType);
    }

    public function testConvertData()
    {
        $userId = 111111;
        $orderId = 222222;
        $token = 'courseOrder:9:333:123:1524324352:c9a10dc1737f63a43d2ca6d155155999:dBosdWlh2mWCauQzO94D0w7IIOs=';
        $mockedOrderService = $this->mockBiz(
            'Order:OrderService',
            array(
                array(
                    'functionName' => 'findOrderItemsByOrderId',
                    'withParams' => array($orderId),
                    'returnValue' => array(
                        array(
                            'create_extra' => array('distributorToken' => $token),
                            'target_type' => 'course',
                            'target_id' => 9,
                            'refund_id' => 3,
                            'status' => 'refunded',
                        ),
                    ),
                ),
                array(
                    'functionName' => 'findOrderItemDeductsByOrderId',
                    'withParams' => array($orderId),
                    'returnValue' => array(
                        array(
                            'deduct_type' => 'vip',
                            'deduct_type_name' => 'vip_deduct_type',
                            'deduct_amount' => 100,
                        ),
                    ),
                ),
            )
        );

        $mockedUserService = $this->mockBiz(
            'User:UserService',
            array(
                array(
                    'functionName' => 'getUser',
                    'withParams' => array($userId),
                    'returnValue' => array(
                        'nickname' => 'nickname_test',
                        'verifiedMobile' => '13675226221',
                    ),
                ),
            )
        );

        $mockedOrderRefundService = $this->mockBiz(
            'Order:OrderRefundService',
            array(
                array(
                    'functionName' => 'getOrderRefundById',
                    'withParams' => array(3),
                    'returnValue' => array('reason' => 'dsdfk'),
                ),
            )
        );

        $order = array(
            'id' => $orderId,
            'user_id' => $userId,
            'title' => 'order title',
            'sn' => 'order sn',
            'created_time' => '1524324352',
            'pay_time' => '1524324352',
            'expired_refund_days' => '1',
            'refund_deadline' => '1524324352',
            'price_amount' => '102',
            'pay_amount' => '2',
            'status' => 'refunded',
            'updated_time' => '1524324352',
        );

        $result = ReflectionUtils::invokeMethod(
            $this->getDistributorCourseOrderService(),
            'convertData',
            array($order)
        );

        $this->assertEquals($token, $result['token']);
        $this->assertEquals('nickname_test', $result['nickname']);
        $this->assertEquals('13675226221', $result['mobile']);
        $this->assertEquals('dsdfk', $result['refundedReason']);

        $mockedOrderService->shouldHaveReceived('findOrderItemsByOrderId')->times(2);
        $mockedOrderService->shouldHaveReceived('findOrderItemDeductsByOrderId')->times(1);
        $mockedUserService->shouldHaveReceived('getUser')->times(1);
    }

    private function getDistributorCourseOrderService()
    {
        return $this->createService('Distributor:DistributorCourseOrderService');
    }
}
