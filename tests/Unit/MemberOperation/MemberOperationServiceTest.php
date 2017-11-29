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

    public function testGetRecord()
    {
        $this->mockBiz(
            'MemberOperation:MemberOperationRecordDao',
            array(
                array(
                    'functionName' => 'get',
                    'returnValue' => array('id' => 111, 'title' => 'title'),
                    'withParams' => array(111),
                ),
            )
        );
        $result = $this->getMemberOperationService()->getRecord(111);

        $this->assertEquals(array('id' => 111, 'title' => 'title'), $result);
    }

    public function testCreateRecord()
    {
        $this->mockBiz(
            'MemberOperation:MemberOperationRecordDao',
            array(
                array(
                    'functionName' => 'create',
                    'returnValue' => array('id' => 111, 'title' => 'title'),
                    'withParams' => array(array(
                        'id' => 111, 
                        'title' => 'title', 
                        'member_id' => 1, 
                        'target_type' => 'course', 
                        'operate_type' => 'join',
                    )),
                ),
            )
        );
        $result = $this->getMemberOperationService()->createRecord(array(
            'id' => 111, 
            'title' => 'title', 
            'member_id' => 1, 
            'target_type' => 'course', 
            'operate_type' => 'join',
        ));
        $this->assertEquals(array('id' => 111, 'title' => 'title'), $result);
    }

    public function testUpdateRefundInfoByOrderId()
    {
        $this->mockBiz(
            'MemberOperation:MemberOperationRecordDao',
            array(
                array(
                    'functionName' => 'getRecordByOrderIdAndType',
                    'returnValue' => array('id' => 111, 'reason' => 'site.join_by_free', 'reason_type' => 'free_join'),
                    'withParams' => array(111, 'exit'),
                ),
                array(
                    'functionName' => 'update',
                    'returnValue' => array('id' => 111, 'title' => 'title'),
                    'withParams' => array(
                        111,
                        array(
                            'refund_id' => 1, 
                        )
                    ),
                ),
            )
        );
        $result = $this->getMemberOperationService()->updateRefundInfoByOrderId(
            111,
            array(
                'refund_id' => 1, 
                'reason' => 'site.join_by_free', 
                'reason_type' => 'free_join',
            )
        );
        $this->assertEquals(array('id' => 111, 'title' => 'title'), $result);
    }

    public function testCountRecords()
    {
        $this->mockBiz(
            'MemberOperation:MemberOperationRecordDao',
            array(
                array(
                    'functionName' => 'count',
                    'returnValue' => 20,
                    'withParams' => array(array('operate_type' => 'join')),
                ),
            )
        );
        $result = $this->getMemberOperationService()->countRecords(array('operate_type' => 'join'));

        $this->assertEquals(20, $result);
    }

    public function testSearchRecords()
    {
        $this->mockBiz(
            'MemberOperation:MemberOperationRecordDao',
            array(
                array(
                    'functionName' => 'search',
                    'returnValue' => array(array('id' => 111, 'operate_type' => 'join')),
                    'withParams' => array(array('operate_type' => 'join'), array(), 0, 5),
                ),
            )
        );
        $result = $this->getMemberOperationService()->searchRecords(array('operate_type' => 'join'), array(), 0, 5);

        $this->assertEquals(array(array('id' => 111, 'operate_type' => 'join')), $result);
    }

    public function testCountGroupByDate()
    {
        $this->mockBiz(
            'MemberOperation:MemberOperationRecordDao',
            array(
                array(
                    'functionName' => 'countGroupByDate',
                    'returnValue' => 20,
                    'withParams' => array(array('operate_type' => 'join'), 'ASC', 'operate_time'),
                ),
            )
        );
        $result = $this->getMemberOperationService()->countGroupByDate(array('operate_type' => 'join'), 'ASC');

        $this->assertEquals(20, $result);
    }

    public function testGetRecordByOrderIdAndType()
    {
        $this->mockBiz(
            'MemberOperation:MemberOperationRecordDao',
            array(
                array(
                    'functionName' => 'getRecordByOrderIdAndType',
                    'returnValue' => array('id' => 111, 'operate_type' => 'join'),
                    'withParams' => array(111, 'join'),
                ),
            )
        );
        $result = $this->getMemberOperationService()->getRecordByOrderIdAndType(111, 'join');

        $this->assertEquals(array('id' => 111, 'operate_type' => 'join'), $result);
    }

    public function testCountUserIdsByConditions()
    {
        $this->mockBiz(
            'MemberOperation:MemberOperationRecordDao',
            array(
                array(
                    'functionName' => 'countUserIdsByConditions',
                    'returnValue' => 10,
                    'withParams' => array(array('operate_type' => 'join')),
                ),
            )
        );
        $result = $this->getMemberOperationService()->countUserIdsByConditions(array('operate_type' => 'join'));

        $this->assertEquals(10, $result);
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
