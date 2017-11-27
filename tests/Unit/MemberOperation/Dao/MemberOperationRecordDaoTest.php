<?php

namespace Tests\Unit\MemberOperation\Dao;

use Tests\Unit\Base\BaseDaoTestCase;

class MemberOperationRecordDaoTest extends BaseDaoTestCase
{
    public function testSearch()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(array('member_id' => 2, 'operate_time' => 800000));
        $expected[] = $this->mockDataObject(array('user_id' => 2, 'operate_type' => 'exit'));

        $testConditions = array(
            array(
                'condition' => array(),
                'expectedResults' => $expected,
                'expectedCount' => 3,
            ),
            array(
                'condition' => array('user_ids' => array(1, 2)),
                'expectedResults' => $expected,
                'expectedCount' => 3,
            ),
            array(
                'condition' => array('operate_type' => 'join'),
                'expectedResults' => array($expected[0], $expected[1]),
                'expectedCount' => 2,
            ),
            array(
                'condition' => array('operate_time_GT' => 800000),
                'expectedResults' => array($expected[0], $expected[2]),
                'expectedCount' => 2,
            ),
            array(
                'condition' => array('operate_time_GE' => 800000),
                'expectedResults' => $expected,
                'expectedCount' => 3,
            ),
            array(
                'condition' => array('operate_time_LT' => 850000),
                'expectedResults' => array($expected[1]),
                'expectedCount' => 1,
            ),
            array(
                'condition' => array('target_type' => 'course'),
                'expectedResults' => $expected,
                'expectedCount' => 3,
            ),
            array(
                'condition' => array('exclude_member_id' => 1),
                'expectedResults' => array($expected[1]),
                'expectedCount' => 1,
            ),
            array(
                'condition' => array('member_id' => 1),
                'expectedResults' => array($expected[0], $expected[2]),
                'expectedCount' => 2,
            ),
            array(
                'condition' => array('target_id' => 1),
                'expectedResults' => $expected,
                'expectedCount' => 3,
            ),
        );

        $this->searchTestUtil($this->getDao(), $testConditions, $this->getCompareKeys());
    }

    public function testGetRecordByOrderIdAndType()
    {
        $expected = $this->mockDataObject();
        $result = $this->getDao()->getRecordByOrderIdAndType(0, 'join');
        $this->assertEquals($expected, $result);
    }

    public function testCountUserIdsByConditions()
    {
        $expected1 = $this->mockDataObject();
        $expected2 = $this->mockDataObject();
        $result = $this->getDao()->countUserIdsByConditions(array('target_id' => 1));
        $this->assertEquals(1, $result);
    }

    public function testCountGroupByDate()
    {
        $expected1 = $this->mockDataObject();
        $expected2 = $this->mockDataObject();
        $expected3 = $this->mockDataObject(array('operate_time' => 800000));
        $result = $this->getDao()->countGroupByDate(array('target_id' => 1), 'ASC');
        var_dump($result);
        $this->assertEquals(2, $result[1]['count']);
    }

    protected function getDefaultMockFields()
    {
        return array(
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
        );
    }
}