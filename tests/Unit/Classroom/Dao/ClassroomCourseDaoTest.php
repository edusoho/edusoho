<?php

namespace Tests\Unit\Classroom\Dao;

use Tests\Unit\Base\BaseDaoTestCase;

class ClassroomCourseDaoTest extends BaseDaoTestCase
{
    public function testSearch()
    {
        $expected = array();
        $expected[] = $this->mockDataObject(array('classroomId' => 2));
        $expected[] = $this->mockDataObject(array('courseId' => 2));
        $testCondition = array(
            array(
                'condition' => array(),
                'expectedResults' => $expected,
                'expectedCount' => 2,
                ),
            array(
                'condition' => array('classroomId' => 2),
                'expectedResults' => array($expected[0]),
                'expectedCount' => 1,
                ),
            array(
                'condition' => array('courseId' => 2),
                'expectedResults' => array($expected[1]),
                'expectedCount' => 1,
                ),
            );
        $this->searchTestUtil($this->getDao(), $testCondition, $this->getCompareKeys());
    }

    public function testUpdateByParam()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $res = $this->getDao()->updateByParam(array('courseId' => 1), array('parentCourseId' => 3));
        $expected[] = $this->mockDataObject(array('parentCourseId' => 3));
        $this->assertEquals(1, $res);
    }

    public function testDeleteByClassroomIdAndCourseId()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $res = $this->getDao()->deleteByClassroomIdAndCourseId(1, 1);
        $this->assertEquals(1, $res);
    }

    public function testFindClassroomIdsByCourseId()
    {
        $expected = array();
        $expected[] = $this->mockDataObject(array('classroomId' => 1));
        $expected[] = $this->mockDataObject(array('classroomId' => 2));
        $expected[] = $this->mockDataObject(array('classroomId' => 3));
        $res = $this->getDao()->findClassroomIdsByCourseId(1);
        $this->assertArrayEquals(array(array('classroomId' => '1'), array('classroomId' => '2'), array('classroomId' => '3')), $res);
    }

    public function testGetClassroomIdByCourseId()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $res = $this->getDao()->getClassroomIdByCourseId(1);
        $this->assertArrayEquals(array('classroomId' => '1'), $res);
    }

    public function testGetByCourseSetId()
    {
        $expected = array();
        $expected[] = $this->mockDataObject(array('courseSetId' => 11));
        $res = $this->getDao()->getByCourseSetId(11);
        $this->assertArrayEquals(array('classroomId' => '1'), $res);
    }

    public function testGetByClassroomIdAndCourseId()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $res = $this->getDao()->getByClassroomIdAndCourseId(1, 1);
        $this->assertArrayEquals($expected[0], $res, $this->getCompareKeys());
    }

    public function testDeleteByClassroomId()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $res = $this->getDao()->deleteByClassroomId(1);
        $this->assertEquals(1, $res);
    }

    public function testFindByClassroomIdAndCourseIds()
    {
        $expected = array();
        $expected[] = $this->mockDataObject(array('courseId' => 1));
        $expected[] = $this->mockDataObject(array('courseId' => 2));
        $expected[] = $this->mockDataObject(array('courseId' => 3));
        $res = $this->getDao()->findByClassroomIdAndCourseIds(1, array(1, 2, 3));
        $testFields = $this->getCompareKeys();
        foreach ($res as $key => $result) {
            $this->assertArrayEquals($expected[$key], $result, $testFields);
        }
    }

    public function testFindByClassroomId()
    {
        $expected = array();
        $expected[] = $this->mockDataObject(array('courseId' => 1));
        $expected[] = $this->mockDataObject(array('courseId' => 2));
        $expected[] = $this->mockDataObject(array('courseId' => 3));
        $res = $this->getDao()->findByClassroomId(1);
        $testFields = $this->getCompareKeys();
        foreach ($res as $key => $result) {
            $this->assertArrayEquals($expected[$key], $result, $testFields);
        }
    }

    public function testFindByCoursesIds()
    {
        $expected = array();
        $expected[] = $this->mockDataObject(array('courseId' => 1));
        $expected[] = $this->mockDataObject(array('courseId' => 2));
        $expected[] = $this->mockDataObject(array('courseId' => 3));
        $res = $this->getDao()->findByCoursesIds(array(1, 2, 3));
        $testFields = $this->getCompareKeys();
        foreach ($res as $key => $result) {
            $this->assertArrayEquals($expected[$key], $result, $testFields);
        }
    }

    public function testFindByCourseSetIds()
    {
        $expected = array();
        $expected[] = $this->mockDataObject(array('courseSetId' => 11));
        $expected[] = $this->mockDataObject(array('courseSetId' => 22));
        $res = $this->getDao()->findByCourseSetIds(array(11, 22));
        $testFields = $this->getCompareKeys();

        foreach ($res as $key => $result) {
            $this->assertArrayEquals($expected[$key], $result, $testFields);
        }
    }

    public function testFindEnabledByCoursesIds()
    {
        $expected = array();
        $expected[] = $this->mockDataObject(array('courseId' => 1, 'disabled' => 1));
        $expected[] = $this->mockDataObject(array('courseId' => 2));
        $expected[] = $this->mockDataObject(array('courseId' => 3));
        $res = $this->getDao()->findEnabledByCoursesIds(array(1, 2, 3));
        $testFields = $this->getCompareKeys();
        foreach ($res as $key => $result) {
            $this->assertArrayEquals($expected[$key + 1], $result, $testFields);
        }
    }

    public function testFindActiveCoursesByClassroomId()
    {
        $expected = array();
        $expected[] = $this->mockDataObject(array('courseId' => 1, 'disabled' => 1));
        $expected[] = $this->mockDataObject(array('courseId' => 2));
        $expected[] = $this->mockDataObject(array('courseId' => 3));
        $res = $this->getDao()->findActiveCoursesByClassroomId(1);
        $testFields = $this->getCompareKeys();
        foreach ($res as $key => $result) {
            $this->assertArrayEquals($expected[$key + 1], $result, $testFields);
        }
    }

    public function testCountCourseTasksByClassroomId()
    {
        $result1 = $this->getDao()->countCourseTasksByClassroomId(1);

        $this->assertEquals(0, $result1);

        $expected = array();
        $expected[] = $this->mockDataObject(array('courseId' => 1));
        $expected[] = $this->mockDataObject(array('courseId' => 2));
        $course1 = $this->getCourseDao()->create(array('taskNum' => 3));
        $course2 = $this->getCourseDao()->create(array('taskNum' => 4));
        $result2 = $this->getDao()->countCourseTasksByClassroomId(1);

        $this->assertEquals(7, $result2);
    }

    protected function getDefaultMockFields()
    {
        return array(
            'classroomId' => 1,
            'courseId' => 1,
            'parentCourseId' => 2,
            );
    }

    /**
     * @return CourseDao
     */
    private function getCourseDao()
    {
        return $this->createDao('Course:CourseDao');
    }
}
