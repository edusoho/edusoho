<?php

namespace Tests\Unit\Course\Dao;

use Tests\Unit\Base\BaseDaoTestCase;

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

    public function testGetDefaultCoursesByCourseSetIds()
    {
        $result = $this->getDao()->getDefaultCoursesByCourseSetIds(array());
        $this->assertEquals(array(), $result);

        $expected = array();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(array('isDefault' => 1));
        $result = $this->getDao()->getDefaultCoursesByCourseSetIds(array(1, 2));
        $this->assertArrayEquals($expected[1], $result[0], $this->getCompareKeys());
    }

    public function testFindPriceIntervalByCourseSetIds()
    {
        $result = $this->getDao()->findPriceIntervalByCourseSetIds(array());
        $this->assertEquals(array(), $result);

        $expected = array();
        $expected[] = $this->mockDataObject(array('price' => 2));
        $expected[] = $this->mockDataObject(array('courseSetId' => 2, 'price' => 1));
        $result = $this->getDao()->findPriceIntervalByCourseSetIds(array(1, 2));
        $this->assertEquals(1, $result[1]['minPrice']);
    }

    public function testCountGroupByCourseSetIds()
    {
        $result = $this->getDao()->countGroupByCourseSetIds(array());
        $this->assertEquals(array(), $result);

        $expected = array();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject(array('courseSetId' => 2));
        $result = $this->getDao()->countGroupByCourseSetIds(array(1, 2));
        $this->assertEquals(1, $result[1]['courseNum']);
    }

    public function testAnalysisCourseDataByTime()
    {
        $expected = array();
        $expected[] = $this->mockDataObject();
        $expected[] = $this->mockDataObject();
        $result = $this->getDao()->analysisCourseDataByTime(time() - 5000, time() + 5000);
        $this->assertEquals(2, $result[0]['count']);
    }

    // public function testUpdateMaxRateByCourseSetId()
    // {
    //     $expected = $this->mockDataObject();
    //     $result = $this->getDao()->updateMaxRateByCourseSetId(1, array('title' => 'title'));
    //     $this->assertEquals('title', $result['title']);
    // }

    public function testfindCourseSetIncomesByCourseSetIds()
    {
        $resultResult = array(
          array(
            'courseSetId' => '1',
            'income' => '12.00',
          ),
          array(
            'courseSetId' => '2',
            'income' => '1.00',
          ),
        );
        $this->mockDataObject(array('income' => 1, 'courseSetId' => 2));
        $this->mockDataObject(array('income' => 1));
        $this->mockDataObject(array('income' => 2));
        $this->mockDataObject(array('income' => 4));
        $this->mockDataObject(array('income' => 5));
        $result = $this->getDao()->findCourseSetIncomesByCourseSetIds(array(1, 2));
        $this->assertArrayEquals($resultResult, $result);

        $result = $this->getDao()->findCourseSetIncomesByCourseSetIds(array());
        $this->assertEquals(array(), $result);
    }

    public function testGetMinAndMaxPublishedCoursePriceByCourseSetId()
    {
        $this->mockDataObject(array('courseSetId' => 2, 'price' => 0, 'status' => 'published'));
        $this->mockDataObject(array('price' => 1));
        $this->mockDataObject(array('price' => 2, 'status' => 'published'));
        $this->mockDataObject(array('price' => 3, 'status' => 'published'));

        $price = $this->getDao()->getMinAndMaxPublishedCoursePriceByCourseSetId(1);
        $this->assertEquals(2, $price['minPrice']);

        $this->mockDataObject(array('price' => 0, 'status' => 'published'));
        $price = $this->getDao()->getMinAndMaxPublishedCoursePriceByCourseSetId(1);
        $this->assertEquals(0, $price['minPrice']);
        $this->assertEquals(3, $price['maxPrice']);
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
