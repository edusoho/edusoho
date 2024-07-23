<?php

namespace Tests\Unit\MemberOperation\Dao;

use AppBundle\Common\ArrayToolkit;
use Tests\Unit\Base\BaseDaoTestCase;

class MemberOperationRecordDaoTest extends BaseDaoTestCase
{
    public function testSearch()
    {
        $expected = [];
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(['member_id' => 2, 'operate_time' => 800000]);
        $expected[] = $this->mockDataObject(['user_id' => 2, 'operate_type' => 'exit', 'reason_type' => 'buy_join']);
        $testConditions = [
            [
                'condition' => [],
                'expectedResults' => $expected,
                'expectedCount' => 3,
            ],
            [
                'condition' => ['id' => $expected[0]['id']],
                'expectedResults' => [$expected[0]],
                'expectedCount' => 1,
            ],
            [
                'condition' => ['user_ids' => [1, 2]],
                'expectedResults' => $expected,
                'expectedCount' => 3,
            ],
            [
                'condition' => ['operate_type' => 'join'],
                'expectedResults' => [$expected[0], $expected[1]],
                'expectedCount' => 2,
            ],
            [
                'condition' => ['operate_time_GT' => 800000],
                'expectedResults' => [$expected[0], $expected[2]],
                'expectedCount' => 2,
            ],
            [
                'condition' => ['operate_time_GE' => 800000],
                'expectedResults' => $expected,
                'expectedCount' => 3,
            ],
            [
                'condition' => ['operate_time_LT' => 850000],
                'expectedResults' => [$expected[1]],
                'expectedCount' => 1,
            ],
            [
                'condition' => ['target_type' => 'course'],
                'expectedResults' => $expected,
                'expectedCount' => 3,
            ],
            [
                'condition' => ['exclude_member_id' => 1],
                'expectedResults' => [$expected[1]],
                'expectedCount' => 1,
            ],
            [
                'condition' => ['member_id' => 1],
                'expectedResults' => [$expected[0], $expected[2]],
                'expectedCount' => 2,
            ],
            [
                'condition' => ['target_id' => 1],
                'expectedResults' => $expected,
                'expectedCount' => 3,
            ],
            [
                'condition' => ['exclude_reason_type' => 'buy_join'],
                'expectedResults' => [$expected[0], $expected[1]],
                'expectedCount' => 2,
            ],
        ];

        $this->searchTestUtil($this->getDao(), $testConditions, $this->getCompareKeys());
    }

    public function testGetRecordByOrderIdAndType()
    {
        $expected = $this->mockDataObject();
        $result = $this->getDao()->getRecordByOrderIdAndType(0, 'join');
        $result[0]['data'] = json_decode($result[0]['data'], true);
        $this->assertEquals($expected, $result[0]);
    }

    public function testCountUserIdsByConditions()
    {
        $expected1 = $this->mockDataObject();
        $expected2 = $this->mockDataObject();
        $result = $this->getDao()->countUserIdsByConditions(['target_id' => 1]);
        $this->assertEquals(1, $result);
    }

    public function testCountGroupByDate()
    {
        $expected1 = $this->mockDataObject();
        $expected2 = $this->mockDataObject();
        $expected3 = $this->mockDataObject(['operate_time' => 800000]);
        $result = $this->getDao()->countGroupByDate(['target_id' => 1], 'ASC');

        $this->assertEquals(2, $result[1]['count']);
    }

    public function testCountGroupByUserId()
    {
        $expected1 = $this->mockDataObject();
        $expected2 = $this->mockDataObject(['user_id' => '2']);
        $expected3 = $this->mockDataObject(['user_id' => '2', 'target_type' => 'classroom', 'target_id' => 3]);
        $expected4 = $this->mockDataObject(['user_id' => '2', 'target_type' => 'classroom', 'target_id' => 3]);
        $expected5 = $this->mockDataObject(['user_id' => '2', 'target_type' => 'classroom', 'target_id' => 5]);

        $result = $this->getDao()->countGroupByUserId('target_id', ['operate_type' => 'join']);
        $result = ArrayToolkit::index($result, 'user_id');
        $this->assertEquals(3, $result[2]['count']);
        $this->assertEquals(1, $result[1]['count']);

        $result = $this->getDao()->countGroupByUserId('target_id', ['operate_type' => 'exit']);
        $this->assertEmpty($result);

        $result = $this->getDao()->countGroupByUserId('target_id', ['operate_type' => 'join', 'user_id' => 1]);
        $result = ArrayToolkit::index($result, 'user_id');

        $this->assertTrue(empty($result[2]));
        $this->assertEquals(1, $result[1]['count']);

        $result = $this->getDao()->countGroupByUserId('target_id', ['operate_type' => 'join', 'user_id' => 1]);
        $result = ArrayToolkit::index($result, 'user_id');
        $this->assertTrue(empty($result[2]));
        $this->assertEquals(1, $result[1]['count']);

        $result = $this->getDao()->countGroupByUserId('target_id', ['operate_type' => 'join', 'target_type' => 'classroom']);
        $result = ArrayToolkit::index($result, 'user_id');
        $this->assertTrue(empty($result[1]));
        $this->assertEquals(2, $result[2]['count']);

        $result = $this->getDao()->countGroupByUserId('target_id', ['operate_type' => 'join', 'user_ids' => [1]]);
        $result = ArrayToolkit::index($result, 'user_id');
        $this->assertTrue(empty($result[2]));

        $result = $this->getDao()->countGroupByUserId('id', ['operate_type' => 'join', 'user_ids' => [1]]);
        $this->assertTrue(empty($result));
    }

    protected function getDefaultMockFields()
    {
        return [
            'title' => 'testTitle',
            'member_id' => 1,
            'user_id' => 1,
            'member_type' => 'student',
            'target_id' => 1,
            'target_type' => 'course',
            'operate_type' => 'join',
            'operate_time' => 900000,
            'operator_id' => 1,
            'data' => 'test',
            'order_id' => 0,
            'refund_id' => 0,
            'reason' => 'site.join_by_free',
            'reason_type' => 'free_join',
        ];
    }
}
