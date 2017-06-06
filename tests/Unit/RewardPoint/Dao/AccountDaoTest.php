<?php

namespace Tests\Unit\RewardPoint\Dao;

use Tests\Unit\Base\BaseDaoTestCase;

class AccountDaoTest extends BaseDaoTestCase
{
    public function testDeleteByUserId()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(array('userId' => 2));

        $res = array();
        $res[] = $this->getDao()->deleteByUserId(1);
        $res[] = $this->getDao()->deleteByUserId(2);

        $this->assertEquals($res, array(true, true));
    }

    public function testGetByUserId()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(array('userId' => 2));

        $res = array();
        $res[] = $this->getDao()->getByUserId(1);
        $res[] = $this->getDao()->getByUserId(2);
        $this->assertEquals($expected, $res);
    }

    public function testSearch()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(array('userId' => 2));
        $expected[] = $this->mockDataObject(array('userId' => 3));

        $testConditions = array(
            array(
                'condition' => array('userId' => 1),
                'expectedResults' => array($expected[0]),
                'expectedCount' => 1
            ),
            array(
                'condition' => array('userIds' => array(1, 2, 3)),
                'expectedResults' => $expected,
                'expectedCount' => 3
            ),
        );

        $this->searchTestUtil($this->getDao(), $testConditions, $this->getCompareKeys());
    }

    public function getDefaultMockFields()
    {
        return array(
            'userId' => 1,
            'balance' => 1000
        );
    }
}