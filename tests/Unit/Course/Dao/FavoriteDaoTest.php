<?php

namespace Tests\Unit\Course\Dao;

use Tests\Unit\Base\BaseDaoTestCase;

class FavoriteDaoTest extends BaseDaoTestCase
{
    public function testGetByUserIdAndCourseId()
    {
        $expected = $this->mockDataObject();

        $res = $this->getDao()->getByUserIdAndCourseId(1, 1);

        $this->assertEquals($expected, $res);
    }

    // 覆盖searchByUserId
    public function testSearch()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(array('userId' => 2));
        $expected[] = $this->mockDataObject(array('courseId' => 2, 'courseSetId' => 2));

        $testConditions = array(
            array(
                'condition' => array('courseId' => 1),
                'expectedResults' => array($expected[0], $expected[1]),
                'expectedCount' => 2,
            ),
            array(
                'condition' => array('userId' => 1),
                'expectedResults' => array($expected[0], $expected[2]),
                'expectedCount' => 2,
            ),
            array(
                'condition' => array('type' => 'course'),
                'expectedResults' => $expected,
                'expectedCount' => 3,
            ),
            array(
                'condition' => array('courseSetId' => 1),
                'expectedResults' => array($expected[0], $expected[1]),
                'expectedCount' => 2,
            ),
            array(
                'condition' => array('courseSetIds' => array(1, 2)),
                'expectedResults' => $expected,
                'expectedCount' => 3,
            ),
            array(
                'condition' => array('excludeCourseIds' => array(1)),
                'expectedResults' => array($expected[2]),
                'expectedCount' => 1,
            ),
        );

        $this->searchTestUtil($this->getDao(), $testConditions, $this->getCompareKeys());
    }

    public function testGetByUserIdAndCourseSetId()
    {
        $expected = $this->mockDataObject();

        $res = $this->getDao()->getByUserIdAndCourseSetId(1, 1);

        $this->assertEquals($expected, $res);
    }

    public function testCountByUserId()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(array('userId' => 2));
        $expected[] = $this->mockDataObject(array('courseId' => 2));

        $res = $this->getDao()->countByUserId(1);

        $this->assertEquals(2, $res);
    }

    public function testSearchByUserId()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(array('userId' => 2));
        $result = $this->getDao()->searchByUserId(2, 0, 5);
        $this->assertArrayEquals($expected[1], $result[0], $this->getCompareKeys());
    }

    public function testFindCourseFavoritesNotInClassroomByUserId()
    {
        $courseSet = $this->mockCourseSet();
        $expected = array();
        $expected[] = $this->mockDataObject(array('createdTime' => 1000));
        $expected[] = $this->mockDataObject();
        $result = $this->getDao()->findCourseFavoritesNotInClassroomByUserId(1, 0, 5);
        $this->assertArrayEquals($expected[1], $result[0], $this->getCompareKeys());
    }

    public function testFindUserFavoriteCoursesNotInClassroomWithCourseType()
    {
        $courseSet = $this->mockCourseSet();
        $course = $this->mockCourse();
        $expected = $this->mockDataObject();
        $result = $this->getDao()->findUserFavoriteCoursesNotInClassroomWithCourseType(1, 'course', 0, 5);
        $this->assertEquals(1, $result[0]['id']);
    }

    public function testCountUserFavoriteCoursesNotInClassroomWithCourseType()
    {
        $courseSet = $this->mockCourseSet();
        $course = $this->mockCourse();
        $expected = $this->mockDataObject();
        $result = $this->getDao()->countUserFavoriteCoursesNotInClassroomWithCourseType(1, 'course');
        $this->assertEquals(1, $result);
    }

    protected function getDefaultMockFields()
    {
        return array(
            'courseId' => 1,
            'userId' => 1,
            'type' => 'course',
            'courseSetId' => 1,
        );
    }

    private function mockCourseSet($fields = array())
    {
        $defaultFields = array(
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

        $fields = array_merge($defaultFields, $fields);

        return $this->getCourseSetDao()->create($fields);
    }

    private function mockCourse($fields = array())
    {
        $defaultFields = array(
            'courseSetId' => 1,
            'title' => 'a',
            'address' => 'a',
        );

        $fields = array_merge($defaultFields, $fields);

        return $this->getCourseDao()->create($fields);
    }

    protected function getCourseSetDao()
    {
        return $this->createDao('Course:CourseSetDao');
    }

    protected function getCourseDao()
    {
        return $this->createDao('Course:CourseDao');
    }
}
