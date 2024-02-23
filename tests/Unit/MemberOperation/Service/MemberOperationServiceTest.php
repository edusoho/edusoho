<?php

namespace Tests\Unit\MemberOperation\Service;

use Biz\BaseTestCase;

class MemberOperationServiceTest extends BaseTestCase
{
    public function testGetJoinReasonByOrderId()
    {
        $reason = $this->getMemberOperationService()->getJoinReasonByOrderId(2);
        $this->assertEquals('site.join_by_free', $reason['reason']);
        $this->assertEquals('free_join', $reason['reason_type']);

        $this->mockBiz('Order:OrderService', [
           ['functionName' => 'getOrder', 'returnValue' => $this->getOrder()],
        ]);
        $reason = $this->getMemberOperationService()->getJoinReasonByOrderId(1);
        $this->assertEquals('site.join_by_purchase', $reason['reason']);
        $this->assertEquals('buy_join', $reason['reason_type']);

        $this->mockBiz('Order:OrderService', [
           ['functionName' => 'getOrder', 'returnValue' => ['source' => 'outside']],
        ]);
        $reason = $this->getMemberOperationService()->getJoinReasonByOrderId(1);
        $this->assertEquals('site.join_by_import', $reason['reason']);
        $this->assertEquals('import_join', $reason['reason_type']);

        $this->mockBiz('Order:OrderService', [
           ['functionName' => 'getOrder', 'returnValue' => ['source' => 'markting']],
        ]);
        $reason = $this->getMemberOperationService()->getJoinReasonByOrderId(1);
        $this->assertEquals('site.join_by_markting', $reason['reason']);
        $this->assertEquals('markting_join', $reason['reason_type']);

        $this->mockBiz('Order:OrderService', [
           ['functionName' => 'getOrder', 'returnValue' => ['source' => 'self', 'pay_amount' => 0]],
        ]);
        $reason = $this->getMemberOperationService()->getJoinReasonByOrderId(1);
        $this->assertEquals('site.join_by_free', $reason['reason']);
        $this->assertEquals('free_join', $reason['reason_type']);
    }

    public function testGetRecord()
    {
        $this->mockBiz(
            'MemberOperation:MemberOperationRecordDao',
            [
                [
                    'functionName' => 'get',
                    'returnValue' => ['id' => 111, 'title' => 'title'],
                    'withParams' => [111],
                ],
            ]
        );
        $result = $this->getMemberOperationService()->getRecord(111);

        $this->assertEquals(['id' => 111, 'title' => 'title'], $result);
    }

    public function testCreateRecord()
    {
        $this->mockBiz(
            'MemberOperation:MemberOperationRecordDao',
            [
                [
                    'functionName' => 'create',
                    'returnValue' => ['id' => 111, 'title' => 'title'],
                    'withParams' => [[
                        'id' => 111,
                        'title' => 'title',
                        'member_id' => 1,
                        'target_type' => 'course',
                        'operate_type' => 'join',
                    ]],
                ],
            ]
        );
        $result = $this->getMemberOperationService()->createRecord([
            'id' => 111,
            'title' => 'title',
            'member_id' => 1,
            'target_type' => 'course',
            'operate_type' => 'join',
        ]);
        $this->assertEquals(['id' => 111, 'title' => 'title'], $result);
    }

    public function testUpdateRefundInfoByOrderId()
    {
        $this->mockBiz(
            'MemberOperation:MemberOperationRecordDao',
            [
                [
                    'functionName' => 'getRecordByOrderIdAndType',
                    'returnValue' => [['id' => 111, 'reason' => 'site.join_by_free', 'reason_type' => 'free_join']],
                    'withParams' => [111, 'exit'],
                ],
                [
                    'functionName' => 'batchUpdate',
                    'withParams' => [
                        [111],
                        [
                            '111' => ['refund_id' => 1],
                        ],
                    ],
                ],
            ]
        );
        $this->getMemberOperationService()->updateRefundInfoByOrderId(
            111,
            [
                'refund_id' => 1,
                'reason' => 'site.join_by_free',
                'reason_type' => 'free_join',
            ]
        );
    }

    public function testCountRecords()
    {
        $this->mockBiz(
            'MemberOperation:MemberOperationRecordDao',
            [
                [
                    'functionName' => 'count',
                    'returnValue' => 20,
                    'withParams' => [['operate_type' => 'join']],
                ],
            ]
        );
        $result = $this->getMemberOperationService()->countRecords(['operate_type' => 'join']);

        $this->assertEquals(20, $result);
    }

    public function testSearchRecords()
    {
        $this->mockBiz(
            'MemberOperation:MemberOperationRecordDao',
            [
                [
                    'functionName' => 'search',
                    'returnValue' => [['id' => 111, 'operate_type' => 'join']],
                    'withParams' => [['operate_type' => 'join'], [], 0, 5],
                ],
            ]
        );
        $result = $this->getMemberOperationService()->searchRecords(['operate_type' => 'join'], [], 0, 5);

        $this->assertEquals([['id' => 111, 'operate_type' => 'join']], $result);
    }

    public function testCountGroupByDate()
    {
        $this->mockBiz(
            'MemberOperation:MemberOperationRecordDao',
            [
                [
                    'functionName' => 'countGroupByDate',
                    'returnValue' => 20,
                    'withParams' => [['operate_type' => 'join'], 'ASC', 'operate_time'],
                ],
            ]
        );
        $result = $this->getMemberOperationService()->countGroupByDate(['operate_type' => 'join'], 'ASC');

        $this->assertEquals(20, $result);
    }

    public function testGetRecordByOrderIdAndType()
    {
        $this->mockBiz(
            'MemberOperation:MemberOperationRecordDao',
            [
                [
                    'functionName' => 'getRecordByOrderIdAndType',
                    'returnValue' => ['id' => 111, 'operate_type' => 'join'],
                    'withParams' => [111, 'join'],
                ],
            ]
        );
        $result = $this->getMemberOperationService()->getRecordByOrderIdAndType(111, 'join');

        $this->assertEquals(['id' => 111, 'operate_type' => 'join'], $result);
    }

    public function testCountUserIdsByConditions()
    {
        $this->mockBiz(
            'MemberOperation:MemberOperationRecordDao',
            [
                [
                    'functionName' => 'countUserIdsByConditions',
                    'returnValue' => 10,
                    'withParams' => [['operate_type' => 'join']],
                ],
            ]
        );
        $result = $this->getMemberOperationService()->countUserIdsByConditions(['operate_type' => 'join']);

        $this->assertEquals(10, $result);
    }

    public function getOrder()
    {
        return [
            'source' => 'self',
            'price_amount' => 1,
        ];
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
