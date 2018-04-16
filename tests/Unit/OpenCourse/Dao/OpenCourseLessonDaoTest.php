<?php

namespace Tests\Unit\OpenCourse\Dao;

use Tests\Unit\Base\BaseDaoTestCase;

class OpenCourseLessonDaoTest extends BaseDaoTestCase
{
    public function testSearch()
    {
        $expected = array();
        $expected[0] = $this->mockDataObject(array('courseId' => 2));
        $expected[1] = $this->mockDataObject(array('updatedTime' => 2));
        $expected[2] = $this->mockDataObject(array('status' => 'unpublished'));
        $expected[3] = $this->mockDataObject(array('type' => 'b'));
        $expected[4] = $this->mockDataObject(array('free' => 2));
        $expected[5] = $this->mockDataObject(array('userId' => 2));
        $expected[6] = $this->mockDataObject(array('mediaId' => 2));
        $expected[7] = $this->mockDataObject(array('number' => 2));
        $expected[8] = $this->mockDataObject(array('startTime' => 2));
        $expected[9] = $this->mockDataObject(array('endTime' => 1));
        $expected[10] = $this->mockDataObject(array('title' => 'b'));
        $expected[11] = $this->mockDataObject(array('createdTime' => 2));
        $expected[12] = $this->mockDataObject(array('copyId' => 2));
        $testCondition = array(
            array(
                'condition' => array(),
                'expectedResults' => $expected,
                'expectedCount' => 13,
                ),
            array(
                'condition' => array('courseId' => 2),
                'expectedResults' => array($expected[0]),
                'expectedCount' => 1,
                ),
            array(
                'condition' => array('updatedTime_GE' => 2),
                'expectedResults' => array($expected[1]),
                'expectedCount' => 1,
                ),
            array(
                'condition' => array('status' => 'unpublished'),
                'expectedResults' => array($expected[2]),
                'expectedCount' => 1,
                ),
            array(
                'condition' => array('type' => 'b'),
                'expectedResults' => array($expected[3]),
                'expectedCount' => 1,
                ),
            array(
                'condition' => array('free' => 2),
                'expectedResults' => array($expected[4]),
                'expectedCount' => 1,
                ),
            array(
                'condition' => array('userId' => 2),
                'expectedResults' => array($expected[5]),
                'expectedCount' => 1,
                ),
            array(
                'condition' => array('mediaId' => 2),
                'expectedResults' => array($expected[6]),
                'expectedCount' => 1,
                ),
            array(
                'condition' => array('number' => 2),
                'expectedResults' => array($expected[7]),
                'expectedCount' => 1,
                ),
            array(
                'condition' => array('startTimeGreaterThan' => 2),
                'expectedResults' => array($expected[8]),
                'expectedCount' => 1,
                ),
            array(
                'condition' => array('endTimeLessThan' => 2),
                'expectedResults' => $expected,
                'expectedCount' => 13,
                ),
            array(
                'condition' => array('startTimeLessThan' => 2),
                'expectedResults' => $expected,
                'expectedCount' => 13,
                ),
            array(
                'condition' => array('endTimeGreaterThan' => 0),
                'expectedResults' => $expected,
                'expectedCount' => 13,
                ),
            array(
                'condition' => array('titleLike' => 'b'),
                'expectedResults' => array($expected[10]),
                'expectedCount' => 1,
                ),
            array(
                'condition' => array('startTime' => '2', 'endTime' => '3'),
                'expectedResults' => array($expected[11]),
                'expectedCount' => 1,
                ),
            array(
                'condition' => array('copyId' => 2),
                'expectedResults' => array($expected[12]),
                'expectedCount' => 1,
                ),
            array(
                'condition' => array('courseIds' => array(2)),
                'expectedResults' => array($expected[0]),
                'expectedCount' => 1,
                ),
            );
        $this->searchTestUtil($this->getDao(), $testCondition, $this->getCompareKeys());
    }

    public function testFindByIds()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject();
        $res = $this->getDao()->findByIdS(array(1, 2, 3));
        $testFields = $this->getCompareKeys();
        foreach ($res as $key => $result) {
            $this->assertArrayEquals($expected[$key], $result, $testFields);
        }
    }

    public function testFindByCourseId()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $res = $this->getDao()->findByCourseId(1);
        $this->assertArrayEquals($expected[0], $res[0], $this->getCompareKeys());
    }

    public function testDeleteByCourseId()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $res = $this->getDao()->deleteByCourseId(1);
        $this->assertEquals(1, $res);
    }

    public function testFindTimeSlotOccupiedLessonsByCourseId()
    {
        $expected = array();
        $expected[] = $this->mockDataObject(array('title' => 'b'));
        $expected[] = $this->mockDataObject(array('title' => 'c'));
        $res = $this->getDao()->findTimeSlotOccupiedLessonsByCourseId(1, 0, 3);
        $testFields = $this->getCompareKeys();
        $this->assertArrayEquals($expected[0], $res[0], $testFields);
        $this->assertArrayEquals($expected[1], $res[1], $testFields);
    }

    public function testFindFinishedLivesWithinTwoHours()
    {
        $expected = array();
        $expected[1] = $this->mockDataObject(array('startTime' => time() - 3600 * 4, 'endTime' => time() - 3600 * 3));
        $expected[2] = $this->mockDataObject(array('startTime' => time() - 3600, 'endTime' => time() - 1800));

        $res = $this->getDao()->findFinishedLivesWithinTwoHours();
        $testFields = $this->getCompareKeys();

        $this->assertArrayEquals($expected[2], $res[0], $testFields);
        $this->assertEquals(1, count($res));
    }

    public function testGetLessonMaxSeqByCourseId()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(array('seq' => 1));
        $res = $this->getDao()->getLessonMaxSeqByCourseId(1);
        $this->assertEquals(1, $res);
    }

    protected function getDefaultMockFields()
    {
        return array(
            'courseId' => 1,
            'updatedTime' => 1,
            'status' => 'published',
            'type' => 'liveOpen',
            'free' => 1,
            'userId' => 1,
            'mediaId' => 1,
            'number' => 1,
            'startTime' => 1,
            'endTime' => 1,
            'title' => 'a',
            'createdTime' => 1,
            'copyId' => 1,
            'replayStatus' => 'ungenerated',
            'progressStatus' => 'created',
        );
    }
}
