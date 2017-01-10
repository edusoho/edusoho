<?php

namespace Tests\Course\Dao;

use Tests\Base\BaseDaoTestCase;

class CourseDaoTest extends BaseDaoTestCase
{
    public function testFindCoursesByCourseSetId()
    {
        $expectedResults[0] = $this->mockCourse();
        $expectedResults[1] = $this->mockCourse();
        $results = $this->getCourseDao()->findCoursesByCourseSetId(1);

        foreach ($results as $key => $result) {
            $this->assertArrayEquals($expectedResults[$key], $result, $this->getCompareKeys());
        }
    }

    public function testGetDefaultCourseByCourseSetId()
    {
        $this->mockCourse();
        $expectedResult = $this->mockCourse(array('isDefault' => 1));
        $result = $this->getCourseDao()->getDefaultCourseByCourseSetId(1);

        $this->assertArrayEquals($expectedResult, $result, $this->getCompareKeys());
    }

    protected function getDefaultMockFields()
    {
        return array(
            'courseSetId' => 1,
            'title' => 'a',
            'address' => 'a',
        );
    }

    protected function getCompareKeys()
    {
        $default = $this->getDefaultMockFields();
        return array_keys($default);
    }

    protected function mockCourse($fields = array())
    {
        $fields = array_merge($this->getDefaultMockFields(), $fields);
        return $this->getCourseDao()->create($fields);
    }

    protected function getCourseDao()
    {
        return $this->getBiz()->dao('Course:CourseDao');
    }
}
