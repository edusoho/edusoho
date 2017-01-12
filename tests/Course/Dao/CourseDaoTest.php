<?php

namespace Tests\Course\Dao;

use Tests\Base\BaseDaoTestCase;

class CourseDaoTest extends BaseDaoTestCase
{
    public function testFindCoursesByCourseSetId()
    {
        $expectedResults[0] = $this->mockDataObject();
        $expectedResults[1] = $this->mockDataObject();
        $results = $this->getDao()->findCoursesByCourseSetIdAndStatus(1);

        foreach ($results as $key => $result) {
            $this->assertArrayEquals($expectedResults[$key], $result, $this->getCompareKeys());
        }
    }

    public function testGetDefaultCourseByCourseSetId()
    {
        $this->mockDataObject();
        $expectedResult = $this->mockDataObject(array('isDefault' => 1));
        $result = $this->getDao()->getDefaultCourseByCourseSetId(1);

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
}
