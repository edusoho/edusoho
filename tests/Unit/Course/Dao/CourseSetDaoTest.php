<?php

namespace Tests\Unit\Course\Dao;

use Tests\Unit\Base\BaseDaoTestCase;

class CourseSetDaoTest extends BaseDaoTestCase
{
    public function testSearch()
    {
        $expected = [];
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject();

        $testConditions = [
            [
                'condition' => ['ids' => range(1, 3)],
                'expectedResults' => $expected,
                'expectedCount' => 3,
            ],
            [
                'condition' => ['status' => 'draft'],
                'expectedResults' => $expected,
                'expectedCount' => 3,
            ],
        ];

        $this->searchTestUtil($this->getDao(), $testConditions, $this->getCompareKeys());
    }

    public function testFindByIds()
    {
        $expected = [];
        for ($i = 0; $i < 10; ++$i) {
            $expected[] = $this->mockDataObject();
        }

        $res = $this->getDao()->findByIds(range(1, 10));

        $this->assertEquals($expected, $res);
    }

    public function testFindLikeTitle()
    {
        $expected = [];
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(['title' => 'mm']);
        $expected[] = $this->mockDataObject(['title' => 'hehe']);

        $res = $this->getDao()->findLikeTitle('m');

        $this->assertEquals([$expected[0], $expected[1]], $res);
    }

    public function testFindLinkEmptyTitle()
    {
        $expected = [];
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(['title' => 'mm']);
        $expected[] = $this->mockDataObject(['title' => 'hehe']);

        $res = $this->getDao()->findLikeTitle(null);

        $this->assertEquals([$expected[0], $expected[1], $expected[2]], $res);
    }

    public function testAnalysisCourseSetDataByTime()
    {
        $count = 10;
        $expectedResult = [];

        $startTime = time();
        for ($i = 0; $i < $count; ++$i) {
            $data = $this->getDefaultMockFields();
            $this->getDao()->create($data);
            $expectedResult = $this->makeExpectedResult($expectedResult);
        }
        $endTime = time();

        $result = $this->getDao()->analysisCourseSetDataByTime($startTime, $endTime);

        $this->assertArrayEquals(array_values($expectedResult), $result);
    }

    private function makeExpectedResult($expectedResult)
    {
        if (empty($expectedResult[date('Y-m-d')])) {
            $expectedResult[date('Y-m-d')] = [
                'count' => 1,
                'date' => date('Y-m-d'),
            ];
        } else {
            $expectedResult[date('Y-m-d')] = [
                'count' => ++$expectedResult[date('Y-m-d')]['count'],
                'date' => date('Y-m-d'),
            ];
        }

        return $expectedResult;
    }

    protected function getDefaultMockFields()
    {
        return [
            'type' => 'course',
            'title' => 'hmm',
            'subtitle' => 'oh',
            'status' => 'draft',
            'serializeMode' => 'none',
            'ratingNum' => 1,
            'rating' => 1,
            'noteNum' => 1,
            'studentNum' => 1,
        ];
    }
}
