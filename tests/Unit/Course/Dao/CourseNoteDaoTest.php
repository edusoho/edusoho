<?php

namespace Tests\Unit\Course\Dao;

use Tests\Unit\Base\BaseDaoTestCase;

class CourseNoteDaoTest extends BaseDaoTestCase
{
    public function testGetByUserIdAndTaskId()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(array('userId' => 2));
        $expected[] = $this->mockDataObject(array('taskId' => 2));

        $res = array();
        $res[] = $this->getDao()->getByUserIdAndTaskId(1, 1);
        $res[] = $this->getDao()->getByUserIdAndTaskId(2, 1);
        $res[] = $this->getDao()->getByUserIdAndTaskId(1, 2);

        foreach ($expected as $key => $val) {
            $this->assertEquals($val, $res[$key]);
        }
    }

    public function testFindByUserIdAndStatus()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(array('userId' => 2));
        $expected[] = $this->mockDataObject(array('status' => 0));

        $res = array();
        $res[] = $this->getDao()->findByUserIdAndStatus(1, 1);
        $res[] = $this->getDao()->findByUserIdAndStatus(2, 1);
        $res[] = $this->getDao()->findByUserIdAndStatus(1, 0);

        foreach ($expected as $key => $val) {
            $this->assertEquals(array($val), $res[$key]);
        }
    }

    public function testFindByUserIdAndCourseId()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(array('userId' => 2));

        $res = array();
        $res[] = $this->getDao()->findByUserIdAndCourseId(1, 1);
        $res[] = $this->getDao()->findByUserIdAndCourseId(2, 1);
        $res[] = $this->getDao()->findByUserIdAndCourseId(1, 2);

        $param = $expected[1]['createdTime'] > $expected[0]['createdTime'] ?
            array($expected[1], $expected[0]) : array($expected[0], $expected[1]);
        $this->assertEquals($param, $res[0]);
        $this->assertEquals(array($expected[2]), $res[1]);
        $this->assertEquals(array(), $res[2]);
    }

    public function testCountByUserIdAndCourseId()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(array('userId' => 2));

        $res = array();
        $res[] = $this->getDao()->countByUserIdAndCourseId(1, 1);
        $res[] = $this->getDao()->countByUserIdAndCourseId(2, 1);
        $res[] = $this->getDao()->countByUserIdAndCourseId(1, 2);

        $this->assertEquals(2, $res[0]);
        $this->assertEquals(1, $res[1]);
        $this->assertEquals(0, $res[2]);
    }

    public function testSearch()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(array('courseId' => 2, 'userId' => 2, 'taskId' => 2, 'content' => 'iiii', 'courseSetId' => 2));
        $expected[] = $this->mockDataObject(array('userId' => 2, 'taskId' => 2, 'content' => 'iiii', 'courseSetId' => 2));
        $expected[] = $this->mockDataObject(array('taskId' => 2, 'content' => 'iiii', 'courseSetId' => 2));
        $expected[] = $this->mockDataObject(array('content' => 'iiii', 'courseSetId' => 2));
        $expected[] = $this->mockDataObject(array('courseSetId' => 2));

        $testConditions = array(
            array(
                'condition' => array(
                    'courseId' => 1,
                ),
                'expectedResults' => array($expected[0], $expected[2], $expected[3], $expected[4], $expected[5]),
                'expectedCount' => 5,
            ),
            array(
                'condition' => array(
                    'userId' => 2,
                ),
                'expectedResults' => array($expected[1], $expected[2]),
                'expectedCount' => 2,
            ),
            array(
                'condition' => array(
                    'taskId' => 2,
                ),
                'expectedResults' => array($expected[1], $expected[2], $expected[3]),
                'expectedCount' => 3,
            ),
            array(
                'condition' => array(
                    'content' => 'i',
                ),
                'expectedResults' => array($expected[1], $expected[2], $expected[3], $expected[4]),
                'expectedCount' => 4,
            ),
            array(
                'condition' => array(
                    'courseSetId' => 2,
                ),
                'expectedResults' => array($expected[1], $expected[2], $expected[3], $expected[4], $expected[5]),
                'expectedCount' => 5,
            ),
        );
    }

    protected function getDefaultMockFields()
    {
        return array(
            'userId' => 1,
            'courseId' => 1,
            'taskId' => 1,
            'content' => 'asdf',
            'length' => 4,
            'likeNum' => 1,
            'status' => 1,
            'courseSetId' => 1,
        );
    }
}
