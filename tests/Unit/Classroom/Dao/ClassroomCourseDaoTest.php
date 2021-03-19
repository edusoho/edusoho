<?php

namespace Tests\Unit\Classroom\Dao;

use Tests\Unit\Base\BaseDaoTestCase;

class ClassroomCourseDaoTest extends BaseDaoTestCase
{
    public function testSearch()
    {
        $expected = [];
        $expected[] = $this->mockDataObject(['classroomId' => 2]);
        $expected[] = $this->mockDataObject(['courseId' => 2]);
        $testCondition = [
            [
                'condition' => [],
                'expectedResults' => $expected,
                'expectedCount' => 2,
            ],
            [
                'condition' => ['classroomId' => 2],
                'expectedResults' => [$expected[0]],
                'expectedCount' => 1,
            ],
            [
                'condition' => ['courseId' => 2],
                'expectedResults' => [$expected[1]],
                'expectedCount' => 1,
            ],
        ];
        $this->searchTestUtil($this->getDao(), $testCondition, $this->getCompareKeys());
    }

    public function testUpdateByParam()
    {
        $expected = [];
        $expected[] = $this->mockDataObject();
        $res = $this->getDao()->updateByParam(['courseId' => 1], ['parentCourseId' => 3]);
        $expected[] = $this->mockDataObject(['parentCourseId' => 3]);
        $this->assertEquals(1, $res);
    }

    public function testDeleteByClassroomIdAndCourseId()
    {
        $expected = [];
        $expected[] = $this->mockDataObject();
        $res = $this->getDao()->deleteByClassroomIdAndCourseId(1, 1);
        $this->assertEquals(1, $res);
    }

    public function testFindClassroomIdsByCourseId()
    {
        $expected = [];
        $expected[] = $this->mockDataObject(['classroomId' => 1]);
        $expected[] = $this->mockDataObject(['classroomId' => 2]);
        $expected[] = $this->mockDataObject(['classroomId' => 3]);
        $res = $this->getDao()->findClassroomIdsByCourseId(1);
        $this->assertArrayEquals([['classroomId' => '1'], ['classroomId' => '2'], ['classroomId' => '3']], $res);
    }

    public function testGetClassroomIdByCourseId()
    {
        $expected = [];
        $expected[] = $this->mockDataObject();
        $res = $this->getDao()->getClassroomIdByCourseId(1);
        $this->assertArrayEquals(['classroomId' => '1'], $res);
    }

    public function testGetByCourseSetId()
    {
        $expected = [];
        $expected[] = $this->mockDataObject(['courseSetId' => 11]);
        $res = $this->getDao()->getByCourseSetId(11);
        $this->assertArrayEquals(['classroomId' => '1'], $res);
    }

    public function testGetByClassroomIdAndCourseId()
    {
        $expected = [];
        $expected[] = $this->mockDataObject();
        $res = $this->getDao()->getByClassroomIdAndCourseId(1, 1);
        $this->assertArrayEquals($expected[0], $res, $this->getCompareKeys());
    }

    public function testDeleteByClassroomId()
    {
        $expected = [];
        $expected[] = $this->mockDataObject();
        $res = $this->getDao()->deleteByClassroomId(1);
        $this->assertEquals(1, $res);
    }

    public function testFindByClassroomIdAndCourseIds()
    {
        $expected = [];
        $expected[] = $this->mockDataObject(['courseId' => 1]);
        $expected[] = $this->mockDataObject(['courseId' => 2]);
        $expected[] = $this->mockDataObject(['courseId' => 3]);
        $res = $this->getDao()->findByClassroomIdAndCourseIds(1, [1, 2, 3]);
        $testFields = $this->getCompareKeys();
        foreach ($res as $key => $result) {
            $this->assertArrayEquals($expected[$key], $result, $testFields);
        }
    }

    public function testFindByClassroomId()
    {
        $expected = [];
        $expected[] = $this->mockDataObject(['courseId' => 1]);
        $expected[] = $this->mockDataObject(['courseId' => 2]);
        $expected[] = $this->mockDataObject(['courseId' => 3]);
        $res = $this->getDao()->findByClassroomId(1);
        $testFields = $this->getCompareKeys();
        foreach ($res as $key => $result) {
            $this->assertArrayEquals($expected[$key], $result, $testFields);
        }
    }

    public function testFindByCoursesIds()
    {
        $expected = [];
        $expected[] = $this->mockDataObject(['courseId' => 1]);
        $expected[] = $this->mockDataObject(['courseId' => 2]);
        $expected[] = $this->mockDataObject(['courseId' => 3]);
        $res = $this->getDao()->findByCoursesIds([1, 2, 3]);
        $testFields = $this->getCompareKeys();
        foreach ($res as $key => $result) {
            $this->assertArrayEquals($expected[$key], $result, $testFields);
        }
    }

    public function testFindByCourseSetIds()
    {
        $expected = [];
        $expected[] = $this->mockDataObject(['courseSetId' => 11]);
        $expected[] = $this->mockDataObject(['courseSetId' => 22]);
        $res = $this->getDao()->findByCourseSetIds([11, 22]);
        $testFields = $this->getCompareKeys();

        foreach ($res as $key => $result) {
            $this->assertArrayEquals($expected[$key], $result, $testFields);
        }
    }

    public function testFindEnabledByCoursesIds()
    {
        $expected = [];
        $expected[] = $this->mockDataObject(['courseId' => 1, 'disabled' => 1]);
        $expected[] = $this->mockDataObject(['courseId' => 2]);
        $expected[] = $this->mockDataObject(['courseId' => 3]);
        $res = $this->getDao()->findEnabledByCoursesIds([1, 2, 3]);
        $testFields = $this->getCompareKeys();
        foreach ($res as $key => $result) {
            $this->assertArrayEquals($expected[$key + 1], $result, $testFields);
        }
    }

    public function testFindActiveCoursesByClassroomId()
    {
        $expected = [];
        $expected[] = $this->mockDataObject(['courseId' => 1, 'disabled' => 1]);
        $expected[] = $this->mockDataObject(['courseId' => 2]);
        $expected[] = $this->mockDataObject(['courseId' => 3]);
        $res = $this->getDao()->findActiveCoursesByClassroomId(1);
        $testFields = $this->getCompareKeys();
        foreach ($res as $key => $result) {
            $this->assertArrayEquals($expected[$key + 1], $result, $testFields);
        }
    }

    public function testCountCourseTasksByClassroomId()
    {
        $result1 = $this->getDao()->countCourseTasksByClassroomId(1);

        $this->assertEquals(0, $result1);

        $expected = [];
        $expected[] = $this->mockDataObject(['courseId' => 1]);
        $expected[] = $this->mockDataObject(['courseId' => 2]);
        $course1 = $this->getCourseDao()->create(['taskNum' => 3, 'courseSetId' => 1]);
        $course2 = $this->getCourseDao()->create(['taskNum' => 4, 'courseSetId' => 1]);
        $result2 = $this->getDao()->countCourseTasksByClassroomId(1);

        $this->assertEquals(7, $result2);
    }

    public function testCountTaskNumByClassroomIds()
    {
        $course1 = $this->getCourseDao()->create(['taskNum' => 3, 'courseSetId' => 3, 'compulsoryTaskNum' => 2, 'electiveTaskNum' => 1]);
        $course2 = $this->getCourseDao()->create(['taskNum' => 4, 'courseSetId' => 4, 'compulsoryTaskNum' => 3, 'electiveTaskNum' => 1]);
        $course3 = $this->getCourseDao()->create(['taskNum' => 5, 'courseSetId' => 5, 'compulsoryTaskNum' => 3, 'electiveTaskNum' => 1]);

        $classroomCourse1 = $this->getDao()->create(['classroomId' => 1, 'courseId' => $course1['id'], 'parentCourseId' => 1]);
        $classroomCourse1 = $this->getDao()->create(['classroomId' => 1, 'courseId' => $course2['id'], 'parentCourseId' => 1]);
        $classroomCourse1 = $this->getDao()->create(['classroomId' => 2, 'courseId' => $course3['id'], 'parentCourseId' => 1]);

        $result = $this->getDao()->countTaskNumByClassroomIds([1]);

        $this->assertEquals([[
            'classroomId' => '1',
            'compulsoryTaskNum' => '5',
            'electiveTaskNum' => '2',
        ]], $result);
    }

    protected function getDefaultMockFields()
    {
        return [
            'classroomId' => 1,
            'courseId' => 1,
            'parentCourseId' => 2,
        ];
    }

    /**
     * @return CourseDao
     */
    private function getCourseDao()
    {
        return $this->createDao('Course:CourseDao');
    }
}
