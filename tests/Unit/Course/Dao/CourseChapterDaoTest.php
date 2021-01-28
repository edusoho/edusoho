<?php

namespace Tests\Unit\Course\Dao;

use Tests\Unit\Base\BaseDaoTestCase;

class CourseChapterDaoTest extends BaseDaoTestCase
{
    public function testFindChaptersByCourseId()
    {
        $activity1 = $this->mockDataObject(array('title' => 'a'));
        $activity2 = $this->mockDataObject(array('title' => 'b'));
        $expectedResults = array($activity1, $activity2);
        $results = $this->getDao()->findChaptersByCourseId(1);

        foreach ($results as $key => $result) {
            $this->assertArrayEquals($expectedResults[$key], $result, $this->getCompareKeys());
        }
    }

    public function testGetChapterCountByCourseIdAndType()
    {
        $this->mockDataObject(array('title' => 'a'));
        $this->mockDataObject(array('title' => 'b'));
        $expectedResult = 2;
        $result = $this->getDao()->getChapterCountByCourseIdAndType(1, 'a');

        $this->assertEquals($expectedResult, $result);
    }

    public function testGetLastChapterByCourseIdAndType()
    {
        $this->mockDataObject(array('seq' => 1));
        $this->mockDataObject(array('seq' => 2));
        $expectedResult = $this->mockDataObject(array('seq' => 3, 'title' => 'a'));
        $result = $this->getDao()->getLastChapterByCourseIdAndType(1, 'a');

        $this->assertArrayEquals($expectedResult, $result, $this->getCompareKeys());
    }

    public function testGetLastChapterByCourseId()
    {
        $this->mockDataObject(array('seq' => 1));
        $this->mockDataObject(array('seq' => 2));
        $expectedResult = $this->mockDataObject(array('seq' => 3, 'title' => 'a'));
        $result = $this->getDao()->getLastChapterByCourseId(1);

        $this->assertArrayEquals($expectedResult, $result, $this->getCompareKeys());
    }

    public function testGetChapterMaxSeqByCourseId()
    {
        $this->mockDataObject(array('seq' => 1));
        $this->mockDataObject(array('seq' => 5));
        $expectedResult = 5;
        $result = $this->getDao()->getChapterMaxSeqByCourseId(1);

        $this->assertEquals($expectedResult, $result);
    }

    public function testDeleteChaptersByCourseId()
    {
        $this->mockDataObject(array('seq' => 1));
        $this->mockDataObject(array('seq' => 5));
        $result1 = $this->getDao()->deleteChaptersByCourseId(2);
        $result2 = $this->getDao()->deleteChaptersByCourseId(1);

        $this->assertEquals(false, $result1);
        $this->assertEquals(true, $result2);
    }

    public function testFindChaptersByCopyIdAndLockedCourseIds()
    {
        $activity1 = $this->mockDataObject(array('courseId' => 1));
        $activity2 = $this->mockDataObject(array('courseId' => 2));
        $this->mockDataObject(array('courseId' => 3));
        $expectedResults = array($activity1, $activity2);
        $ids = array(1, 2);
        $results = $this->getDao()->findChaptersByCopyIdAndLockedCourseIds(1, $ids);

        foreach ($results as $key => $result) {
            $this->assertArrayEquals($expectedResults[$key], $result, $this->getCompareKeys());
        }
    }

    protected function getDefaultMockFields()
    {
        return array(
            'courseId' => 1,
            'type' => 'a',
            'number' => 1,
            'seq' => 1,
            'title' => 's',
            'copyId' => 1,
        );
    }
}
