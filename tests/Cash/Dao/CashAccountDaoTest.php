<?php

namespace Tests\Cash\Dao;

use Tests\Base\BaseDaoTestCase;

class CashAccountDaoTest extends BaseDaoTestCase
{
    public function testSearch()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $testCondition = array(
            array(
                'condition' => array('id' => 1),
                'expectedResults' => $expected,
                'expectedCount' => 1
                )
            );
        $this->searchTestUtil($this->getDao(), $testCondition, $this->getCompareKeys());
    }

    public function testGetByUserId()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $res = $this->getDao()->getByUserId(1);
        $this->assertArrayEquals($expected[0], $res, $this->getCompareKeys());
    }

    public function testFindByUserIds()
    {
        $expected = array();
        $expected[] = $this->mockDataObject(array('userId' => 1));
        $expected[] = $this->mockDataObject(array('userId' => 2));
        $expected[] = $this->mockDataObject(array('userId' => 3));
        $res = $this->getDao()->findByUserIds(array(1, 2, 3));
        $testFields = $this->getCompareKeys();
        foreach ($res as $key => $result) {
            $this->assertArrayEquals($expected[$key], $result, $testFields);
        }
    }

    public function testWaveCashField()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $this->getDao()->waveCashField(1, 3.4);
        $res = $this->getDao()->getByUserId(1);
        $this->assertEquals('70.0', $res['cash']);
    }

    public function testWaveDownCashField()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $this->getDao()->waveDownCashField(1, 3.4);
        $res = $this->getDao()->getByUserId(1);
        $this->assertEquals('63.2', $res['cash']);
    }
    
    protected function getDefaultMockfields()
    {
        return array(
            'userId' => 1,
            'cash' => 66.6
            );
    }
}
