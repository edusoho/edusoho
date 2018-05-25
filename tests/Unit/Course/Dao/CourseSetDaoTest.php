<?php

namespace Tests\Unit\Course\Dao;

use Tests\Unit\Base\BaseDaoTestCase;

class CourseSetDaoTest extends BaseDaoTestCase
{
    public function testSearch()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject();

        $testConditions = array(
            array(
                'condition' => array('ids' => range(1, 3)),
                'expectedResults' => $expected,
                'expectedCount' => 3,
            ),
            array(
                'condition' => array('status' => 'draft'),
                'expectedResults' => $expected,
                'expectedCount' => 3,
            ),
        );

        $this->searchTestUtil($this->getDao(), $testConditions, $this->getCompareKeys());
    }

    public function testFindByIds()
    {
        $expected = array();
        for ($i = 0; $i < 10; ++$i) {
            $expected[] = $this->mockDataObject();
        }

        $res = $this->getDao()->findByIds(range(1, 10));

        $this->assertEquals($expected, $res);
    }

    public function testFindLikeTitle()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(array('title' => 'mm'));
        $expected[] = $this->mockDataObject(array('title' => 'hehe'));

        $res = $this->getDao()->findLikeTitle('m');

        $this->assertEquals(array($expected[0], $expected[1]), $res);
    }

    public function testFindLinkEmptyTitle()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(array('title' => 'mm'));
        $expected[] = $this->mockDataObject(array('title' => 'hehe'));

        $res = $this->getDao()->findLikeTitle(null);

        $this->assertEquals(array($expected[0], $expected[1], $expected[2]), $res);
    }

    public function testAnalysisCourseSetDataByTime()
    {
        $count = 10;

        while (true) {
            $startTime = time();
            $endTime = $startTime + 30;
            if (date('Y-m-d', $startTime) == date('Y-m-d', $endTime)) {
                for ($i = 0; $i < $count; ++$i) {
                    $createdTime = $startTime + $i;
                    $data = array_merge(
                        $this->getDefaultMockFields(),
                        array(
                            'createdTime' => $createdTime,
                            'updatedTime' => $createdTime,
                        )
                    );
                    $this->getDao()->create($data);
                }
                $result = $this->getDao()->analysisCourseSetDataByTime($startTime, $startTime + 30);
                break;
            }

            sleep(30);
        }

        $expectedResult = array(array(
            'count' => $count,
            'date' => date('Y-m-d', $startTime),
        ));
        $this->assertArrayEquals($expectedResult, $result);
    }

    protected function getDefaultMockFields()
    {
        return array(
            'type' => 'course',
            'title' => 'hmm',
            'subtitle' => 'oh',
            'status' => 'draft',
            'serializeMode' => 'none',
            'ratingNum' => 1,
            'rating' => 1,
            'noteNum' => 1,
            'studentNum' => 1,
        );
    }
}
