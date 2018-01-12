<?php

namespace Tests\Unit\Group\Dao;

use Tests\Unit\Base\BaseDaoTestCase;

class ThreadCollectDaoTest extends BaseDaoTestCase
{
    public function testSearch()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(array('userId' => 2));
        $expected[] = $this->mockDataObject(array('threadId' => 3));

        $testConditions = array(
            array(
                'condition' => array(),
                'expectedResults' => $expected,
                'expectedCount' => 3,
            ),
            array(
                'condition' => array('userId' => 2),
                'expectedResults' => array($expected[1]),
                'expectedCount' => 1,
            ),
            array(
                'condition' => array('threadId' => 3),
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

    public function testDeleteByUserIdAndThreadId()
    {
        $expected = $this->mockDataObject();
        $this->getDao()->deleteByUserIdAndThreadId(3, 2);
        $result = $this->getDao()->getByUserIdAndThreadId(3, 2);
        $this->assertNull($result);
    }

    protected function getDefaultMockFields()
    {
        return array(
            'threadId' => 2,
            'userId' => 3,
            'createdTime' => 10000,
        );
    }
}
