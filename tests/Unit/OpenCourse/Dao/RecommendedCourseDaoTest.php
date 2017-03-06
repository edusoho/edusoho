<?php

namespace Tests\Unit\OpenCourse\Dao;

use Tests\Unit\Base\BaseDaoTestCase;

class RecommendedCourseDaoTest extends BaseDaoTestCase
{
    public function testSearch()
    {
        $expected = array();
        $expected[] = $this->mockDataObject(array('openCourseId' => 2));
        $expected[] = $this->mockDataObject(array('recommendCourseId' => 2));
        $expected[] = $this->mockDataObject(array('type' => 'b'));
        $testCondition = array(
            array(
                'condition' => array(),
                'expectedResults' => $expected,
                'expectedCount' => 3,
                ),
            array(
                'condition' => array('openCourseId' => 2),
                'expectedResults' => array($expected[0]),
                'expectedCount' => 1,
                ),
            array(
                'condition' => array('recommendCourseId' => 2),
                'expectedResults' => array($expected[1]),
                'expectedCount' => 1,
                ),
            array(
                'condition' => array('type' => 'b'),
                'expectedResults' => array($expected[2]),
                'expectedCount' => 1,
                ),
            );
        $this->searchTestUtil($this->getDao(), $testCondition, $this->getCompareKeys());
    }

    public function testGetByCourseIdAndType()
    {
        $expected = array();
        $expected[0] = $this->mockDataObject();
        $res = $this->getDao()->getByCourseIdAndType(1, 1, 'a');
        $this->assertArrayEquals($expected[0], $res, $this->getCompareKeys());
    }

    public function testFindByOpenCourseId()
    {
        $expected = array();
        $expected[0] = $this->mockDataObject();
        $res = $this->getDao()->findByOpenCourseId(1);
        $this->assertEquals(1, count($res));
        $this->assertArrayEquals($expected[0], $res[0], $this->getCompareKeys());
    }

    public function testDeleteByOpenCourseIdAndRecommendCourseId()
    {
        $expected = array();
        $expected[0] = $this->mockDataObject();
        $res = $this->getDao()->deleteByOpenCourseIdAndRecommendCourseId(1, 1);
        $this->assertEquals(1, $res);
    }

    public function testFindRandomRecommendCourses()
    {
        $expected = array();
        $expected[0] = $this->mockDataObject();
        $res = $this->getDao()->findRandomRecommendCourses(1, 2);
        $this->assertEquals(1, count($res));
        $this->assertArrayEquals($expected[0], $res[0], $this->getCompareKeys());
    }

    protected function getDefaultMockFields()
    {
        return array(
            'openCourseId' => 1,
            'recommendCourseId' => 1,
            'type' => 'a',
            );
    }
}
