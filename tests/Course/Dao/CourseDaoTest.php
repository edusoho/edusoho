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

    public function testGetIncomesByCourseSetIds()
    {
        $resultResult = array(
          array(
            "courseSetId"=>"1",
            "income"=> "12.00"
          ),
          array(
            "courseSetId"=>"2",
            "income"=> "1.00"
          )      
        );
        $this->mockDataObject(array('income' => 1, 'courseSetId'=> 2));
        $this->mockDataObject(array('income' => 1));
        $this->mockDataObject(array('income' => 2));
        $this->mockDataObject(array('income' => 4));
        $this->mockDataObject(array('income' => 5));
        $result = $this->getDao()->getIncomesByCourseSetIds(array(1,2));
        $this->assertArrayEquals($resultResult, $result);
    }

    protected function getDefaultMockFields()
    {
        return array(
            'courseSetId' => 1,
            'title' => 'a',
            'address' => 'a'
        );
    }
}
