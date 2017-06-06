<?php

namespace Tests\Unit\RewardPoint\Dao;

use Tests\Unit\Base\BaseDaoTestCase;

class AccountFlowDaoTest extends BaseDaoTestCase
{
    public function testSearch()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(array('sn' => '00002'));
        $expected[] = $this->mockDataObject(array('sn' => '00003'));
        $expected[] = $this->mockDataObject(array('userId' => 2, 'sn' => '00004'));

        $testConditions = array(
            array(
                'condition' => array('userId' => 1),
                'expectedResults' => array($expected[0], $expected[1], $expected[2]),
                'expectedCount' => 3,
            ),
            array(
                'condition' => array('userIds' => array(1, 2)),
                'expectedResults' => $expected,
                'expectedCount' => 4,
            ),
            array(
                'condition' => array('type' => 'inflow'),
                'expectedResults' => $expected,
                'expectedCount' => 4,
            ),
            array(
                'condition' => array('operator' => 1),
                'expectedResults' => $expected,
                'expectedCount' => 4,
            ),
        );

        $this->searchTestUtil($this->getDao(), $testConditions, $this->getCompareKeys());
    }

    public function getDefaultMockFields()
    {
        return array(
            'userId' => 1,
            'sn' => '00001',
            'type' => 'inflow',
            'amount' => 100,
            'operator' => 1
        );
    }
}