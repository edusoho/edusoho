<?php

namespace Tests\Unit\OpenCourse\Dao;

use Tests\Unit\Base\BaseDaoTestCase;

class OpenCourseLessonDaoTest extends BaseDaoTestCase
{
    public function testSearch()
    {
        $expected = [];
        $expected[0] = $this->mockDataObject(['courseId' => 2]);
        $expected[1] = $this->mockDataObject(['updatedTime' => 2]);
        $expected[2] = $this->mockDataObject(['status' => 'unpublished']);
        $expected[3] = $this->mockDataObject(['type' => 'b']);
        $expected[4] = $this->mockDataObject(['free' => 2]);
        $expected[5] = $this->mockDataObject(['userId' => 2]);
        $expected[6] = $this->mockDataObject(['mediaId' => 2]);
        $expected[7] = $this->mockDataObject(['number' => 2]);
        $expected[8] = $this->mockDataObject(['startTime' => 2]);
        $expected[9] = $this->mockDataObject(['endTime' => 1]);
        $expected[10] = $this->mockDataObject(['title' => 'b']);
        $expected[11] = $this->mockDataObject(['createdTime' => 2]);
        $expected[12] = $this->mockDataObject(['copyId' => 2]);
        $testCondition = [
            [
                'condition' => [],
                'expectedResults' => $expected,
                'expectedCount' => 13,
            ],
            [
                'condition' => ['courseId' => 2],
                'expectedResults' => [$expected[0]],
                'expectedCount' => 1,
            ],
            [
                'condition' => ['updatedTime_GE' => 2],
                'expectedResults' => [$expected[1]],
                'expectedCount' => 1,
            ],
            [
                'condition' => ['status' => 'unpublished'],
                'expectedResults' => [$expected[2]],
                'expectedCount' => 1,
            ],
            [
                'condition' => ['type' => 'b'],
                'expectedResults' => [$expected[3]],
                'expectedCount' => 1,
            ],
            [
                'condition' => ['free' => 2],
                'expectedResults' => [$expected[4]],
                'expectedCount' => 1,
            ],
            [
                'condition' => ['userId' => 2],
                'expectedResults' => [$expected[5]],
                'expectedCount' => 1,
            ],
            [
                'condition' => ['mediaId' => 2],
                'expectedResults' => [$expected[6]],
                'expectedCount' => 1,
            ],
            [
                'condition' => ['number' => 2],
                'expectedResults' => [$expected[7]],
                'expectedCount' => 1,
            ],
            [
                'condition' => ['startTimeGreaterThan' => 2],
                'expectedResults' => [$expected[8]],
                'expectedCount' => 1,
            ],
            [
                'condition' => ['endTimeLessThan' => 2],
                'expectedResults' => $expected,
                'expectedCount' => 13,
            ],
            [
                'condition' => ['startTimeLessThan' => 2],
                'expectedResults' => $expected,
                'expectedCount' => 13,
            ],
            [
                'condition' => ['endTimeGreaterThan' => 0],
                'expectedResults' => $expected,
                'expectedCount' => 13,
            ],
            [
                'condition' => ['titleLike' => 'b'],
                'expectedResults' => [$expected[10]],
                'expectedCount' => 1,
            ],
            [
                'condition' => ['startTime' => '2', 'endTime' => '3'],
                'expectedResults' => [$expected[11]],
                'expectedCount' => 1,
            ],
            [
                'condition' => ['copyId' => 2],
                'expectedResults' => [$expected[12]],
                'expectedCount' => 1,
            ],
            [
                'condition' => ['courseIds' => [2]],
                'expectedResults' => [$expected[0]],
                'expectedCount' => 1,
            ],
        ];
        $this->searchTestUtil($this->getDao(), $testCondition, $this->getCompareKeys());
    }

    public function testFindByIds()
    {
        $expected = [];
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject();
        $res = $this->getDao()->findByIdS([1, 2, 3]);
        $testFields = $this->getCompareKeys();
        foreach ($res as $key => $result) {
            $this->assertArrayEquals($expected[$key], $result, $testFields);
        }
    }

    public function testFindByCourseId()
    {
        $expected = [];
        $expected[] = $this->mockDataObject();
        $res = $this->getDao()->findByCourseId(1);
        $this->assertArrayEquals($expected[0], $res[0], $this->getCompareKeys());
    }

    public function testDeleteByCourseId()
    {
        $expected = [];
        $expected[] = $this->mockDataObject();
        $res = $this->getDao()->deleteByCourseId(1);
        $this->assertEquals(1, $res);
    }

    public function testFindTimeSlotOccupiedLessonsByCourseId()
    {
        $expected = [];
        $expected[] = $this->mockDataObject(['title' => 'b']);
        $expected[] = $this->mockDataObject(['title' => 'c']);
        $res = $this->getDao()->findTimeSlotOccupiedLessonsByCourseId(1, 0, 3);
        $testFields = $this->getCompareKeys();
        $this->assertArrayEquals($expected[0], $res[0], $testFields);
        $this->assertArrayEquals($expected[1], $res[1], $testFields);
    }

    public function testGetLessonMaxSeqByCourseId()
    {
        $expected = [];
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(['seq' => 1]);
        $res = $this->getDao()->getLessonMaxSeqByCourseId(1);
        $this->assertEquals(1, $res);
    }

    public function testSearchLessonsWithOrder()
    {
        $lesson1 = $this->mockDataObject([
            'courseId' => 1,
            'updatedTime' => time(),
            'status' => 'published',
            'type' => 'liveOpen',
            'free' => 1,
            'userId' => 1,
            'mediaId' => 1,
            'number' => 1,
            'startTime' => time(),
            'endTime' => time() + 60 * 60,
            'title' => 'a',
            'createdTime' => time(),
            'copyId' => 1,
            'replayStatus' => 'ungenerated',
            'progressStatus' => 'created',
        ]);

        $lesson2 = $this->mockDataObject([
            'courseId' => 2,
            'updatedTime' => time() + 60 * 60 * 2,
            'status' => 'published',
            'type' => 'liveOpen',
            'free' => 1,
            'userId' => 1,
            'mediaId' => 1,
            'number' => 1,
            'startTime' => time() + 60 * 60 * 2,
            'endTime' => time() + 60 * 60 * 3,
            'title' => 'a',
            'createdTime' => time() + 60 * 60 * 2,
            'copyId' => 1,
            'replayStatus' => 'ungenerated',
            'progressStatus' => 'created',
        ]);

        $lesson3 = $this->mockDataObject([
            'courseId' => 3,
            'updatedTime' => time() - 60 * 60 * 2,
            'status' => 'published',
            'type' => 'liveOpen',
            'free' => 1,
            'userId' => 1,
            'mediaId' => 1,
            'number' => 1,
            'startTime' => time() - 60 * 60 * 2,
            'endTime' => time() - 60 * 60,
            'title' => 'a',
            'createdTime' => time() - 60 * 60 * 2,
            'copyId' => 1,
            'replayStatus' => 'ungenerated',
            'progressStatus' => 'created',
        ]);

        $result = $this->getDao()->searchLessonsWithOrderBy(['courseIds' => [$lesson1['courseId']]], 0, 2);

        $this->assertCount(1, $result);
        $this->assertEquals($lesson1['id'], $result[0]['id']);
    }

    protected function getDefaultMockFields()
    {
        return [
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
        ];
    }
}
