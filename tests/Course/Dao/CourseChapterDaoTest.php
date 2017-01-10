<?php

namespace Tests\Course\Dao;

use Tests\Base\BaseDaoTestCase;

class CourseChapterDaoTest extends BaseDaoTestCase
{
    public function testFindChaptersByCourseId()
    {
        $activity1 = $this->mockCourseChapter(array('title' => 'a'));
        $activity2 = $this->mockCourseChapter(array('title' => 'b'));
        $expectedResults = array($activity1, $activity2);
        $results = $this->getCourseChapterDao()->findChaptersByCourseId(1);

        foreach ($results as $key => $result) {
            $this->assertArrayEquals($expectedResults[$key], $result, $this->getCompareKeys());
        }
    }

    public function testGetChapterCountByCourseIdAndType()
    {
        $this->mockCourseChapter(array('title' => 'a'));
        $this->mockCourseChapter(array('title' => 'b'));
        $expectedResult = 2;
        $result = $this->getCourseChapterDao()->getChapterCountByCourseIdAndType(1, 'a');

        $this->assertEquals($expectedResult, $result);
    }

    public function testGetChapterCountByCourseIdAndTypeAndParentId()
    {
        $this->mockCourseChapter(array('title' => 'a'));
        $this->mockCourseChapter(array('title' => 'b'));
        $expectedResult = 2;
        $result = $this->getCourseChapterDao()->getChapterCountByCourseIdAndTypeAndParentId(1, 'a', 1);

        $this->assertEquals($expectedResult, $result);
    }

    public function testGetLastChapterByCourseIdAndType()
    {
        $this->mockCourseChapter(array('seq' => 1));
        $this->mockCourseChapter(array('seq' => 2));
        $expectedResult = $this->mockCourseChapter(array('seq' => 3, 'title' => 'a'));
        $result = $this->getCourseChapterDao()->getLastChapterByCourseIdAndType(1, 'a');

        $this->assertArrayEquals($expectedResult, $result, $this->getCompareKeys());
    }

    public function testGetLastChapterByCourseId()
    {
        $this->mockCourseChapter(array('seq' => 1));
        $this->mockCourseChapter(array('seq' => 2));
        $expectedResult = $this->mockCourseChapter(array('seq' => 3, 'title' => 'a'));
        $result = $this->getCourseChapterDao()->getLastChapterByCourseId(1);

        $this->assertArrayEquals($expectedResult, $result, $this->getCompareKeys());
    }

    public function testGetChapterMaxSeqByCourseId()
    {
        $this->mockCourseChapter(array('seq' => 1));
        $this->mockCourseChapter(array('seq' => 5));
        $expectedResult = 5;
        $result = $this->getCourseChapterDao()->getChapterMaxSeqByCourseId(1);

        $this->assertEquals($expectedResult, $result);
    }

    public function testDeleteChaptersByCourseId()
    {
        $this->mockCourseChapter(array('seq' => 1));
        $this->mockCourseChapter(array('seq' => 5));
        $result1 = $this->getCourseChapterDao()->deleteChaptersByCourseId(2);
        $result2 = $this->getCourseChapterDao()->deleteChaptersByCourseId(1);

        $this->assertEquals(false, $result1);
        $this->assertEquals(true, $result2);
    }

    public function testFindChaptersByCopyIdAndLockedCourseIds()
    {
        $activity1 = $this->mockCourseChapter(array('courseId' => 1));
        $activity2 = $this->mockCourseChapter(array('courseId' => 2));
        $this->mockCourseChapter(array('courseId' => 3));
        $expectedResults = array($activity1, $activity2);
        $ids = array(1,2);
        $results = $this->getCourseChapterDao()->findChaptersByCopyIdAndLockedCourseIds(1, $ids);

        foreach ($results as $key => $result) {
            $this->assertArrayEquals($expectedResults[$key], $result, $this->getCompareKeys());
        }
    }

    protected function getDefaultMockFields()
    {
        return array(
            'courseId' => 1,
            'type' => 'a',
            'parentId' => 1,
            'number' => 1,
            'seq' => 1,
            'title' => 's',
            'copyId' => 1
        );
    }

    protected function getCompareKeys()
    {
        $default = $this->getDefaultMockFields();
        return array_keys($default);
    }

    protected function mockCourseChapter($fields)
    {
        $fields = array_merge($this->getDefaultMockFields(), $fields);
        return $this->getCourseChapterDao()->create($fields);
    }

    protected function getCourseChapterDao()
    {
        return $this->getBiz()->dao('Course:CourseChapterDao');
    }
}
