<?php

namespace Tests\Unit\OpenCourse\Dao;

use Tests\Unit\Base\BaseDaoTestCase;

class OpenCourseRecommendedDaoTest extends BaseDaoTestCase
{
    public function testSearch()
    {
        $expected = [];
        $expected[] = $this->mockDataObject(['openCourseId' => 2]);
        $expected[] = $this->mockDataObject(['recommendCourseId' => 2]);
        $expected[] = $this->mockDataObject(['type' => 'b']);
        $testCondition = [
            [
                'condition' => [],
                'expectedResults' => $expected,
                'expectedCount' => 3,
                ],
            [
                'condition' => ['openCourseId' => 2],
                'expectedResults' => [$expected[0]],
                'expectedCount' => 1,
                ],
            [
                'condition' => ['recommendCourseId' => 2],
                'expectedResults' => [$expected[1]],
                'expectedCount' => 1,
                ],
            [
                'condition' => ['type' => 'b'],
                'expectedResults' => [$expected[2]],
                'expectedCount' => 1,
                ],
            ];
        $this->searchTestUtil($this->getDao(), $testCondition, $this->getCompareKeys());
    }

    public function testGetByCourseIdAndType()
    {
        $expected = [];
        $expected[0] = $this->mockDataObject();
        $res = $this->getDao()->getByCourseIdAndType(1, 1, 'a');
        $this->assertArrayEquals($expected[0], $res, $this->getCompareKeys());
    }

    public function testFindByOpenCourseId()
    {
        $expected = [];
        $expected[0] = $this->mockDataObject();
        $res = $this->getDao()->findByOpenCourseId(1);
        $this->assertEquals(1, count($res));
        $this->assertArrayEquals($expected[0], $res[0], $this->getCompareKeys());
    }

    public function testDeleteByOpenCourseIdAndRecommendCourseId()
    {
        $expected = [];
        $expected[0] = $this->mockDataObject();
        $res = $this->getDao()->deleteByOpenCourseIdAndRecommendCourseId(1, 1);
        $this->assertEquals(1, $res);
    }

    public function testFindRandomRecommendCourses()
    {
        $expected = [];
        $expected[0] = $this->mockDataObject();
        $res = $this->getDao()->findRandomRecommendCourses(1, 2);
        $this->assertEquals(1, count($res));
        $this->assertArrayEquals($expected[0], $res[0], $this->getCompareKeys());
    }

    protected function getDefaultMockFields()
    {
        return [
            'openCourseId' => 1,
            'recommendCourseId' => 1,
            'type' => 'a',
            ];
    }
}
