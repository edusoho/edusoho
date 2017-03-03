<?php

namespace Tests\Unit\Classroom\Dao;

use Tests\Unit\Base\BaseDaoTestCase;

class ClassroomReviewDaoTest extends BaseDaoTestCase
{
    public function testSearch()
    {
        $expected = array();
        $expected[] = $this->mockDataObject(array('userId' => 2));
        $expected[] = $this->mockDataObject(array('classroomId' => 2));
        $expected[] = $this->mockDataObject(array('rating' => 2));
        $expected[] = $this->mockDataObject(array('content' => 'qqq'));
        $expected[] = $this->mockDataObject(array('parentId' => 2));
        $testCondition = array(
            array(
                'condition' => array(),
                'expectedResults' => $expected,
                'expectedCount' => 5,
                ),
            array(
                'condition' => array('userId' => 2),
                'expectedResults' => array($expected[0]),
                'expectedCount' => 1,
                ),
            array(
                'condition' => array('classroomId' => 2),
                'expectedResults' => array($expected[1]),
                'expectedCount' => 1,
                ),
            array(
                'condition' => array('rating' => 2),
                'expectedResults' => array($expected[2]),
                'expectedCount' => 1,
                ),
            array(
                'condition' => array('content' => 'qqq'),
                'expectedResults' => array($expected[3]),
                'expectedCount' => 1,
                ),
            array(
                'condition' => array('parentId' => 2),
                'expectedResults' => array($expected[4]),
                'expectedCount' => 1,
                ),
            );
        $this->searchTestUtil($this->getDao(), $testCondition, $this->getCompareKeys());
    }

    public function testSumReviewRatingByClassroomId()
    {
        $expected = array();
        $expected[] = $this->mockDataObject(array('userId' => 1));
        $expected[] = $this->mockDataObject(array('userId' => 2));
        $res = $this->getDao()->sumReviewRatingByClassroomId(1);
        $this->assertEquals(2, $res);
    }

    public function testCountReviewByClassroomId()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $res = $this->getDao()->countReviewByClassroomId(1);
        $this->assertEquals(1, $res);
    }

    public function testGetByUserIdAndClassroomId()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $res = $this->getDao()->getByUserIdAndClassroomId(1, 1);
        $this->assertArrayEquals($expected[0], $res);
    }

    protected function getDefaultMockFields()
    {
        return array(
            'userId' => 1,
            'classroomId' => 1,
            'rating' => 1,
            'content' => 'aaa',
            'parentId' => 0,
            'title' => 'bbb',
            );
    }
}
