<?php

namespace Tests\Unit\OpenCourse\Dao;

use Tests\Unit\Base\BaseDaoTestCase;

class OpenCourseMemberDaoTest extends BaseDaoTestCase
{
    public function testSearch()
    {
        $expected = array();
        $expected[] = $this->mockDataObject(array('userId' => 2));
        $expected[] = $this->mockDataObject(array('courseId' => 2));
        $expected[] = $this->mockDataObject(array('role' => 'teacher'));
        $expected[] = $this->mockDataObject(array('isNotified' => 2));
        $expected[] = $this->mockDataObject(array('mobile' => '2'));
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
                'condition' => array('courseId' => 2),
                'expectedResults' => array($expected[1]),
                'expectedCount' => 1,
                ),
            array(
                'condition' => array('role' => 'teacher'),
                'expectedResults' => array($expected[2]),
                'expectedCount' => 1,
                ),
            array(
                'condition' => array('isNotified' => 2),
                'expectedResults' => array($expected[3]),
                'expectedCount' => 1,
                ),
            array(
                'condition' => array('startTimeGreaterThan' => 2, 'startTimeLessThan' => 1234567891232),
                'expectedResults' => $expected,
                'expectedCount' => 5,
                ),
            array(
                'condition' => array('mobile' => '2'),
                'expectedResults' => array($expected[4]),
                'expectedCount' => 1,
                ),
            array(
                'condition' => array('courseIds' => array(2)),
                'expectedResults' => array($expected[1]),
                'expectedCount' => 1,
                ),
            array(
                'condition' => array('userIds' => array(2)),
                'expectedResults' => array($expected[0]),
                'expectedCount' => 1,
                ),
            );
        $this->searchTestUtil($this->getDao(), $testCondition, $this->getCompareKeys());
    }

    public function testGetByUserIdAndCourseId()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $res = $this->getDao()->getByUserIdAndCourseId(1, 1);
        $testFields = $this->getCompareKeys();
        $this->assertArrayEquals($expected[0], $res, $testFields);
    }

    public function testGetByIpAndCourseId()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $res = $this->getDao()->getByIpAndCourseId(1, 'a');
        $this->assertArrayEquals($expected[0], $res, $this->getCompareKeys());
    }

    public function testGetByMobileAndCourseId()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $res = $this->getDao()->getByMobileAndCourseId('1', 1);
        $this->assertArrayEquals($expected[0], $res, $this->getCompareKeys());
    }

    public function testFindByCourseIds()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject();
        $res = $this->getDao()->findByCourseIds(array(1, 2, 3));
        $testFields = $this->getCompareKeys();
        foreach ($res as $key => $result) {
            $this->assertArrayEquals($expected[$key], $result, $testFields);
        }
    }

    public function testDeleteByCourseId()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $res = $this->getDao()->deleteByCourseId(1);
        $this->assertEquals(1, $res);
    }

    public function testFindByCourseIdAndRole()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $res = $this->getDao()->findByCourseIdAndRole(1, 'student', 0, 2);
        $this->assertEquals(1, count($res));
        $this->assertArrayEquals($expected[0], $res[0], $this->getCompareKeys());
    }

    protected function getDefaultMockFields()
    {
        return array(
            'userId' => 1,
            'courseId' => 1,
            'role' => 'student',
            'isNotified' => 1,
            'createdTime' => 1,
            'mobile' => '1',
            'ip' => 'a',
            );
    }
}
