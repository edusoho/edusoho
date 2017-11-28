<?php

namespace Tests\Unit\OpenCourseRecommendedServiceTest;

use Biz\BaseTestCase;

class MemberOperationServiceTest extends BaseTestCase
{
    public function testGetJoinReasonByOrderId()
    {
        $reason = $this->getMemberOperationService()->getJoinReasonByOrderId(2);
        $this->assertEquals('site.join_by_free', $reason['reason']);
        $this->assertEquals('free_join', $reason['reason_type']);

        $this->mockBiz('Order:OrderService', array(
           array('functionName' => 'getOrder', 'returnValue' => $this->getOrder()),
        ));
        $reason = $this->getMemberOperationService()->getJoinReasonByOrderId(1);
        $this->assertEquals('site.join_by_purchase', $reason['reason']);
        $this->assertEquals('buy_join', $reason['reason_type']);

        $this->mockBiz('Order:OrderService', array(
           array('functionName' => 'getOrder', 'returnValue' => array('source' => 'outside')),
        ));
        $reason = $this->getMemberOperationService()->getJoinReasonByOrderId(1);
        $this->assertEquals('site.join_by_import', $reason['reason']);
        $this->assertEquals('import_join', $reason['reason_type']);

        $this->mockBiz('Order:OrderService', array(
           array('functionName' => 'getOrder', 'returnValue' => array('source' => 'markting')),
        ));
        $reason = $this->getMemberOperationService()->getJoinReasonByOrderId(1);
        $this->assertEquals('site.join_by_markting', $reason['reason']);
        $this->assertEquals('markting_join', $reason['reason_type']);

        $this->mockBiz('Order:OrderService', array(
           array('functionName' => 'getOrder', 'returnValue' => array('source' => 'self', 'pay_amount' => 0)),
        ));
        $reason = $this->getMemberOperationService()->getJoinReasonByOrderId(1);
        $this->assertEquals('site.join_by_free', $reason['reason']);
        $this->assertEquals('free_join', $reason['reason_type']);
    }

    public function getOrder()
    {
        return array(
            'source' => 'self',
            'pay_amount' => 1,
        );
    }

    public function getOrderService()
    {
        return $this->createService('Order:OrderService');
    }

    public function getMemberOperationService()
    {
        return $this->createService('MemberOperation:MemberOperationService');
    }
}
