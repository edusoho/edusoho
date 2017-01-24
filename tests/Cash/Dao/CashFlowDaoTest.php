<?php

namespace Tests\Cash\Dao;

use Tests\Base\BaseDaoTestCase;

class CashFlowDaoTest extends BaseDaoTestCase
{
    public function testSearch()
    {
        $expected = array();
        $expected[] = $this->mockDataObject(array('userId' => 2, 'sn' => 123));
        $expected[] = $this->mockDataObject(array('type' => 'outflow', 'sn' => 124));
        $expected[] = $this->mockDataObject(array('cashType' => 'Coin', 'sn' => 125));
        $expected[] = $this->mockDataObject(array('category' => 'b', 'sn' => 126));
        $expected[] = $this->mockDataObject(array('orderSn' => 'd', 'sn' => 127));
        $testCondition = array(
            array(
                'condition' => array(),
                'expectedResults' => $expected,
                'expectedCount' => 5
                ),
            array(
                'condition' => array('userId' => 2),
                'expectedResults' => array($expected[0]),
                'expectedCount' => 1
                ),
            array(
                'condition' => array('type' => 'outflow'),
                'expectedResults' => array($expected[1]),
                'expectedCount' => 1
                ),
            array(
                'condition' => array('cashType' => 'Coin'),
                'expectedResults' => array($expected[2]),
                'expectedCount' => 1
                ),
            array(
                'condition' => array('category' => 'b'),
                'expectedResults' => array($expected[3]),
                'expectedCount' => 1
                ),
            array(
                'condition' => array('orderSn' => 'd'),
                'expectedResults' => array($expected[4]),
                'expectedCount' => 1
                )
            );
        $this->searchTestUtil($this->getDao(), $testCondition, $this->getCompareKeys());
    }

    public function testGetBySn()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $res = $this->getDao()->getBySn(123);
        $this->assertArrayEquals($expected[0], $res);
    }

    public function testGetByOrderSn()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $res = $this->getDao()->getByOrderSn('c');
        $this->assertArrayEquals($expected[0], $res);
    }

    public function testAnalysisAmount()
    {
        $expected = array();
        $expected[] = $this->mockDataObject(array('amount' => 1.0, 'sn' => 123));
        $expected[] = $this->mockDataObject(array('amount' => 1.0, 'sn' => 124));
        $expected[] = $this->mockDataObject(array('amount' => 1.0, 'sn' => 125));
        $res = $this->getDao()->analysisAmount(array('userId' => 1));
        $this->assertEquals('3.0', $res);
    }

    public function testFindUserIdsByFlows()
    {
        $expected = array();
        $expected[] = $this->mockDataObject(array('amount' => 1.0));
        $expected[] = $this->mockDataObject(array('amount' => 1.0, 'userId' => 2, 'sn' => 124));
        $expected[] = $this->mockDataObject(array('amount' => 1.0, 'userId' => 2, 'sn' => 125));
        $res = $this->getDao()->findUserIdsByFlows('inflow', 0, 'DESC', 0, 100);
        $expected[] = array(array('userId' => '2', 'amounts' => '2.00'), array('userId' => '1', 'amounts' => '1.00'));
        $this->assertArrayEquals($expected[3], $res);
    }

    protected function getDefaultMockfields()
    {
        return array(
            'userId' => 1,
            'type' => 'inflow',
            'cashType' => 'RMB',
            'category' => 'a',
            'sn' => 123,
            'name' => 'b',
            'orderSn' => 'c',
            'createdTime' => 0
            );
    }
}
