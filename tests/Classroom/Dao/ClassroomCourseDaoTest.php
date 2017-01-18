<?php

namespace tests\Classroom\Dao;

use Tests\Base\BaseDaoTestCase;

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
                'expectedCount' => 2
                ),
            array(
                'condition' => array('classroomId' => 2),
                'expectedResults' => array($expected[0]),
                'expectedCount' => 1
                ),
            array(
                'condition' => array('courseId' => 2),
                'expectedResults' => array($expected[1]),
                'expectedCount' => 1
                )
            );
        $this->searchTestUtil($this->getDao(), $testCondition, $this->getCompareKeys());
    }

    // public function testUpdateByParam()
    // {
    //     $expected = array();
    //     $expected[] = $this->mockDataObject();
    //     $res = $this->getDao()->updateByParam(array('courseId' => 1), array('parentCourseId' => 3));
    //     $expected[] = $this->mockDataObject(array('parentCourseId' => 3));
    //     $this->assertArrayEquals($expected[1], $res, $this->getCompareKeys());
    // }

    protected function getDefaultMockFields()
    {
        return array(
            'classroomId' => 1,
            'courseId' => 1,
            'parentCourseId' =>2
            );
    }
}
