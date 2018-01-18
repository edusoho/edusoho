<?php

namespace Tests\Unit\Group\Dao;

use Tests\Unit\Base\BaseDaoTestCase;

class ThreadTradeDaoTest extends BaseDaoTestCase
{
    public function testSearch()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(array('userId' => 4, 'threadId' => 3));
        $expected[] = $this->mockDataObject(array('goodsId' => 4));

        $testConditions = array(
            array(
                'condition' => array(),
                'expectedResults' => $expected,
                'expectedCount' => 3,
            ),
            array(
                'condition' => array('threadId' => 3),
                'expectedResults' => array($expected[1]),
                'expectedCount' => 1,
            ),
            array(
                'condition' => array('userId' => 4),
                'expectedResults' => array($expected[1]),
                'expectedCount' => 1,
            ),
            array(
                'condition' => array('goodsId' => 4),
                'expectedResults' => array($expected[2]),
                'expectedCount' => 1,
            ),
        );

        $this->searchTestUtil($this->getDao(), $testConditions, $this->getCompareKeys());
    }

    public function testGetByUserIdAndThreadId()
    {
        $expected = $this->mockDataObject();
        $result = $this->getDao()->getByUserIdAndThreadId(3, 2);
        $this->assertArrayEquals($expected, $result, $this->getCompareKeys());
    }

    public function testGetByUserIdAndGoodsId()
    {
        $expected = $this->mockDataObject();
        $result = $this->getDao()->getByUserIdAndGoodsId(3, 3);
        $this->assertArrayEquals($expected, $result, $this->getCompareKeys());
    }

    protected function getDefaultMockFields()
    {
        return array(
            'threadId' => 2,
            'goodsId' => 3,
            'userId' => 3,
        );
    }
}
