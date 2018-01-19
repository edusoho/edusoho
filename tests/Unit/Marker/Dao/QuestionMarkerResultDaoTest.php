<?php

namespace Tests\Unit\Marker\Dao;

use Tests\Unit\Base\BaseDaoTestCase;

class QuestionMarkerResultDaoTest extends BaseDaoTestCase
{
    public function testSearch()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(array('userId' => 3, 'markerId' => 3));
        $expected[] = $this->mockDataObject(array('status' => 'right', 'taskId' => 3, 'questionMarkerId' => 3));

        $testConditions = array(
            array(
                'condition' => array(),
                'expectedResults' => $expected,
                'expectedCount' => 3,
            ),
            array(
                'condition' => array('userId' => 3),
                'expectedResults' => array($expected[1]),
                'expectedCount' => 1,
            ),
            array(
                'condition' => array('markerId' => 3),
                'expectedResults' => array($expected[1]),
                'expectedCount' => 1,
            ),
            array(
                'condition' => array('status' => 'right'),
                'expectedResults' => array($expected[2]),
                'expectedCount' => 1,
            ),
            array(
                'condition' => array('taskId' => 3),
                'expectedResults' => array($expected[2]),
                'expectedCount' => 1,
            ),
            array(
                'condition' => array('questionMarkerId' => 3),
                'expectedResults' => array($expected[2]),
                'expectedCount' => 1,
            ),
        );

        $this->searchTestUtil($this->getDao(), $testConditions, $this->getCompareKeys());
    }

    public function testDeleteByQuestionMarkerId()
    {
        $expected = $this->mockDataObject();
        $this->getDao()->deleteByQuestionMarkerId(2);
        $result = $this->getDao()->get(1);
        $this->assertNull($result);
    }

    public function testFindByIds()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(array('userId' => 3));
        $result = $this->getDao()->findByIds(array(1, 2));
        $this->assertArrayEquals($expected[1], $result[1], $this->getCompareKeys());
    }

    public function testFindByUserIdAndMarkerId()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(array('userId' => 3));
        $result = $this->getDao()->findByUserIdAndMarkerId(3, 2);
        $this->assertArrayEquals($expected[1], $result[0], $this->getCompareKeys());
    }

    public function testFindByUserIdAndQuestionMarkerId()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(array('userId' => 3));
        $result = $this->getDao()->findByUserIdAndQuestionMarkerId(3, 2);
        $this->assertArrayEquals($expected[1], $result[0], $this->getCompareKeys());
    }

    public function testCountDistinctUserIdByQuestionMarkerIdAndTaskId()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(array('userId' => 3));
        $result = $this->getDao()->countDistinctUserIdByQuestionMarkerIdAndTaskId(2, 2);
        $this->assertEquals(2, $result);
    }

    public function testCountDistinctUserIdByTaskId()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(array('userId' => 3));
        $result = $this->getDao()->countDistinctUserIdByTaskId(2);
        $this->assertEquals(2, $result);
    }

    public function testFindByTaskIdAndQuestionMarkerId()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(array('taskId' => 3));
        $result = $this->getDao()->findByTaskIdAndQuestionMarkerId(3, 2);
        $this->assertArrayEquals($expected[1], $result[0], $this->getCompareKeys());
    }

    protected function getDefaultMockFields()
    {
        return array(
            'markerId' => 2,
            'questionMarkerId' => 2,
            'taskId' => 2,
            'userId' => 2,
            'status' => 'none',
        );
    }
}
