<?php

namespace Tests\Unit\Task\Dao;

use Tests\Unit\Base\BaseDaoTestCase;

class TaskDaoTest extends BaseDaoTestCase
{
    public function testCountByChpaterId()
    {
        $this->mockDataObject();
        $count = $this->getDao()->countByChpaterId(3);
        $this->assertEquals(1, $count);
        $this->mockDataObject();
        $count = $this->getDao()->countByChpaterId(3);
        $this->assertEquals(2, $count);

        $this->mockDataObject(['categoryId' => 1]);
        $count = $this->getDao()->countByChpaterId(3);
        $this->assertEquals(2, $count);
    }

    public function testDeleteByCategoryId()
    {
        $expected = $this->mockDataObject();
        $this->getDao()->deleteByCategoryId(3);
        $result = $this->getDao()->get(1);
        $this->assertNull($result);
    }

    public function testDeleteByCourseId()
    {
        $expected = $this->mockDataObject();
        $this->assertNotNull($expected);

        $this->getDao()->deleteByCourseId(2);

        $result = $this->getDao()->get($expected['id']);
        $this->assertNull($result);
    }

    public function testFindByCourseId()
    {
        $expected = [];
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(['seq' => 0]);
        $result = $this->getDao()->findByCourseId(2);
        $this->assertArrayEquals($expected[1], $result[0], $this->getCompareKeys());
    }

    public function testFindByCategoryIds()
    {
        $this->mockDataObject();
        $result = $this->getDao()->findByCategoryIds(['3']);

        $this->assertCount(1, $result);
    }

    public function testFindByCourseIds()
    {
        $expected = $this->mockDataObject();
        $result = $this->getDao()->findByCourseIds([2, 3]);
        $this->assertArrayEquals($expected, $result[0], $this->getCompareKeys());
    }

    public function testFindByActivityIds()
    {
        $expected = [];
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(['activityId' => 4]);
        $result = $this->getDao()->findByActivityIds([2, 3]);
        $this->assertArrayEquals($expected[0], $result[0], $this->getCompareKeys());
    }

    public function testFindByCourseSetId()
    {
        $expected = [];
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(['fromCourseSetId' => 4]);
        $result = $this->getDao()->findByCourseSetId(3);
        $this->assertArrayEquals($expected[0], $result[0], $this->getCompareKeys());
    }

    public function testFindByIds()
    {
        $expected = [];
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(['fromCourseSetId' => 4]);
        $result = $this->getDao()->findByIds([1, 3]);
        $this->assertArrayEquals($expected[0], $result[0], $this->getCompareKeys());
    }

    public function testFindByCourseIdAndCategoryId()
    {
        $expected = [];
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(['courseId' => 4]);
        $result = $this->getDao()->findByCourseIdAndCategoryId(2, 3);
        $this->assertArrayEquals($expected[0], $result[0], $this->getCompareKeys());
    }

    public function testGetMaxSeqByCourseId()
    {
        $expected = [];
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(['seq' => 4]);
        $result = $this->getDao()->getMaxSeqByCourseId(2);
        $this->assertEquals(4, $result);
    }

    public function testGetNumberSeqByCourseId()
    {
        $expected = [];
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(['number' => 'number2']);
        $result = $this->getDao()->getNumberSeqByCourseId(2);
        $this->assertEquals('number2', $result);
    }

    public function testGetMinSeqByCourseId()
    {
        $expected = [];
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(['seq' => 4]);
        $result = $this->getDao()->getMinSeqByCourseId(2);
        $this->assertEquals(1, $result);
    }

    public function testGetNextTaskByCourseIdAndSeq()
    {
        $expected = [];
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(['seq' => 4]);
        $result = $this->getDao()->getNextTaskByCourseIdAndSeq(2, 3);
        $this->assertArrayEquals($expected[1], $result, $this->getCompareKeys());
    }

    public function testGetPreTaskByCourseIdAndSeq()
    {
        $expected = [];
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(['seq' => 4]);
        $result = $this->getDao()->getPreTaskByCourseIdAndSeq(2, 3);
        $this->assertArrayEquals($expected[0], $result, $this->getCompareKeys());
    }

    public function testGetByCopyId()
    {
        $expected = [];
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(['copyId' => 4]);
        $result = $this->getDao()->getByCopyId(3);
        $this->assertArrayEquals($expected[0], $result, $this->getCompareKeys());
    }

    public function testGetByCourseIdAndCopyId()
    {
        $expected = [];
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(['copyId' => 4]);
        $result = $this->getDao()->getByCourseIdAndCopyId(2, 3);
        $this->assertArrayEquals($expected[0], $result, $this->getCompareKeys());
    }

    public function testFindByChapterId()
    {
        $expected = [];
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(['seq' => 4]);
        $result = $this->getDao()->findByChapterId(3);
        $this->assertArrayEquals($expected[1], $result[1], $this->getCompareKeys());
    }

    public function testGetByChapterIdAndMode()
    {
        $expected = [];
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(['categoryId' => 4]);
        $result = $this->getDao()->getByChapterIdAndMode(4, 'mode');
        $this->assertArrayEquals($expected[1], $result, $this->getCompareKeys());
    }

    public function testGetByCourseIdAndSeq()
    {
        $expected = [];
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(['courseId' => 3]);
        $result = $this->getDao()->getByCourseIdAndSeq(3, 1);
        $this->assertArrayEquals($expected[1], $result, $this->getCompareKeys());
    }

    public function testFindPastLivedCourseSetIds()
    {
        $expected = $this->mockDataObject(['type' => 'live', 'status' => 'published', 'endTime' => time() - 5000]);
        $result = $this->getDao()->findPastLivedCourseSetIds();
        $this->assertEquals(3, $result[0]['fromCourseSetId']);
    }

    public function testGetTaskByCourseIdAndActivityId()
    {
        $expected = [];
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(['courseId' => 3]);
        $result = $this->getDao()->getTaskByCourseIdAndActivityId(3, 2);
        $this->assertArrayEquals($expected[1], $result, $this->getCompareKeys());
    }

    public function testFindByCourseIdAndIsFree()
    {
        $expected = [];
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(['courseId' => 3]);
        $result = $this->getDao()->findByCourseIdAndIsFree(3, 0);
        $this->assertArrayEquals($expected[1], $result[0], $this->getCompareKeys());
    }

    public function testFindByCopyIdAndLockedCourseIds()
    {
        $expected = [];
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(['courseId' => 4]);
        $result = $this->getDao()->findByCopyIdAndLockedCourseIds(1, []);
        $this->assertEquals([], $result);

        $result = $this->getDao()->findByCopyIdAndLockedCourseIds(3, [3, 4]);
        $this->assertArrayEquals($expected[1], $result[0], $this->getCompareKeys());
    }

    public function testFindByCopyIdSAndLockedCourseIds()
    {
        $expected = [];
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(['courseId' => 4]);
        $result = $this->getDao()->findByCopyIdSAndLockedCourseIds([], []);
        $this->assertEquals([], $result);

        $result = $this->getDao()->findByCopyIdSAndLockedCourseIds([3], [3, 4]);
        $this->assertArrayEquals($expected[1], $result[0], $this->getCompareKeys());
    }

    public function testAnalysisTaskDataByTime()
    {
        $expected = $this->mockDataObject(['createdTime' => 7000]);
        $result = $this->getDao()->analysisTaskDataByTime(5000, 8000);
        $this->assertEquals(1, $result[0]['count']);
    }

    protected function getDefaultMockFields()
    {
        return [
            'courseId' => 2,
            'seq' => 1,
            'categoryId' => 3,
            'activityId' => 2,
            'title' => 'title',
            'isFree' => 0,
            'isOptional' => 0,
            'startTime' => 0,
            'endTime' => 0,
            'mode' => 'mode',
            'status' => 'create',
            'number' => 'number1',
            'type' => 'type',
            'mediaSource' => 'self',
            'maxOnlineNum' => 5,
            'fromCourseSetId' => 3,
            'length' => 10,
            'copyId' => 3,
            'createdUserId' => 2,
        ];
    }

    protected function getCourseSetDao()
    {
        return $this->createDao('Course:CourseSetDao');
    }

    protected function getTaskResultDao()
    {
        return $this->createDao('Task:TaskResultDao');
    }
}
