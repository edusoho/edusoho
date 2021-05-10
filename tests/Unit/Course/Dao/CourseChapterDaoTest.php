<?php

namespace Tests\Unit\Course\Dao;

use Tests\Unit\Base\BaseDaoTestCase;

class CourseChapterDaoTest extends BaseDaoTestCase
{
    public function testFindChaptersByCourseId()
    {
        $activity1 = $this->mockDataObject(['title' => 'a']);
        $activity2 = $this->mockDataObject(['title' => 'b']);
        $expectedResults = [$activity1, $activity2];
        $results = $this->getDao()->findChaptersByCourseId(1);

        foreach ($results as $key => $result) {
            $this->assertArrayEquals($expectedResults[$key], $result, $this->getCompareKeys());
        }
    }

    public function testGetChapterCountByCourseIdAndType()
    {
        $this->mockDataObject(['title' => 'a']);
        $this->mockDataObject(['title' => 'b']);
        $expectedResult = 2;
        $result = $this->getDao()->getChapterCountByCourseIdAndType(1, 'a');

        $this->assertEquals($expectedResult, $result);
    }

    public function testGetLastChapterByCourseIdAndType()
    {
        $this->mockDataObject(['seq' => 1]);
        $this->mockDataObject(['seq' => 2]);
        $expectedResult = $this->mockDataObject(['seq' => 3, 'title' => 'a']);
        $result = $this->getDao()->getLastChapterByCourseIdAndType(1, 'a');

        $this->assertArrayEquals($expectedResult, $result, $this->getCompareKeys());
    }

    public function testGetLastChapterByCourseId()
    {
        $this->mockDataObject(['seq' => 1]);
        $this->mockDataObject(['seq' => 2]);
        $expectedResult = $this->mockDataObject(['seq' => 3, 'title' => 'a']);
        $result = $this->getDao()->getLastChapterByCourseId(1);

        $this->assertArrayEquals($expectedResult, $result, $this->getCompareKeys());
    }

    public function testGetChapterMaxSeqByCourseId()
    {
        $this->mockDataObject(['seq' => 1]);
        $this->mockDataObject(['seq' => 5]);
        $expectedResult = 5;
        $result = $this->getDao()->getChapterMaxSeqByCourseId(1);

        $this->assertEquals($expectedResult, $result);
    }

    public function testDeleteChaptersByCourseId()
    {
        $this->mockDataObject(['seq' => 1]);
        $this->mockDataObject(['seq' => 5]);
        $result1 = $this->getDao()->deleteChaptersByCourseId(2);
        $result2 = $this->getDao()->deleteChaptersByCourseId(1);

        $this->assertEquals(false, $result1);
        $this->assertEquals(true, $result2);
    }

    public function testFindChaptersByCourseIdAndLessonIds()
    {
        $this->mockDataObject(['courseId' => 1]);
        $result = $this->getDao()->findChaptersByCourseIdAndLessonIds(1, ['1']);

        $this->assertCount(1, $result);
    }

    public function testFindChaptersByCopyIdAndLockedCourseIds()
    {
        $activity1 = $this->mockDataObject(['courseId' => 1]);
        $activity2 = $this->mockDataObject(['courseId' => 2]);
        $this->mockDataObject(['courseId' => 3]);
        $expectedResults = [$activity1, $activity2];
        $ids = [1, 2];
        $results = $this->getDao()->findChaptersByCopyIdAndLockedCourseIds(1, $ids);

        foreach ($results as $key => $result) {
            $this->assertArrayEquals($expectedResults[$key], $result, $this->getCompareKeys());
        }
    }

    protected function getDefaultMockFields()
    {
        return [
            'courseId' => 1,
            'type' => 'a',
            'number' => 1,
            'seq' => 1,
            'title' => 's',
            'copyId' => 1,
        ];
    }
}
