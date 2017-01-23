<?php

namespace Tests\Cash\Dao;

use Tests\Base\BaseDaoTestCase;

class CashOrdersDaoTest extends BaseDaoTestCase
{
    public function testSearch()
    {
        $expected = array();
        $expected[] = $this->mockDataObject(array('status' => 'paid', 'sn' => 123));
        $expected[] = $this->mockDataObject(array('userId' => 2, 'sn' => 124));
        $expected[] = $this->mockDataObject(array('payment' => 'q', 'sn' => 125));
        $expected[] = $this->mockDataObject(array('title' => 'w', 'sn' => 126));
        $expected[] = $this->mockDataObject(array('sn' => 127));
        $testCondition = array(
            array(
                'condition' => array(),
                'expectedResults' => $expected,
                'expectedCount' => 5
                ),
            array(
                'condition' => array('status' => 'paid'),
                'expectedResults' => array($expected[0]),
                'expectedCount' => 1
                ),
            array(
                'condition' => array('userId' => 2),
                'expectedResults' => array($expected[1]),
                'expectedCount' => 1
                ),
            array(
                'condition' => array('payment' => 'q'),
                'expectedResults' => array($expected[2]),
                'expectedCount' => 1
                ),
            array(
                'condition' => array('title' => 'w'),
                'expectedResults' => array($expected[3]),
                'expectedCount' => 1
                ),
            array(
                'condition' => array('sn' => 127),
                'expectedResults' => array($expected[4]),
                'expectedCount' => 1
                ),
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

    public function testGetByToken()
    {
        $expected = array();
        $expected[] = $this->mockDataObject(array('token' => 'a'));
        $res = $this->getDao()->getBySn(123);
        $this->assertArrayEquals($expected[0], $res);
    }

    public function testCloseOrders()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $res = $this->getDao()->closeOrders(1);
        $this->assertEquals(1, $res);
    }

    public function testAnalysisAmount()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $res = $this->getDao()->analysisAmount(array('userId' => 1));
        $this->assertEquals(0.00, $res);
    }

    protected function getDefaultMockfields()
    {
        return array(
            'status' => 'created',
            'userId' => 1,
            'payment' => 'a',
            'title' => 'b',
            'createdTime' => 0,
            'note' => 'c',
            'sn' => 123
            );
    }
}
