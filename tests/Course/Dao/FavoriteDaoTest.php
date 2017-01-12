<?php

namespace Tests\Course\Dao;

use Tests\Base\BaseDaoTestCase;

class FavoriteDaoTest extends BaseDaoTestCase
{
    public function testGetByUserIdAndCourseId()
    {
        $factor = $this->mockDataObject();

        $res = $this->getDao()->getByUserIdAndCourseId(1, 1);

        $this->assertEquals($factor, $res);
    }

    // 覆盖searchByUserId
    public function testSearch()
    {
        $factor = array();
        $factor[] = $this->mockDataObject();
        $factor[] = $this->mockDataObject(array('userId' => 2));
        $factor[] = $this->mockDataObject(array('courseId' => 2, 'courseSetId' => 2));
        
        $testConditions = array(
            array(
                'condition' => array('courseId' => 1),
                'expectedResults' => array($factor[0], $factor[1]),
                'expectedCount' => 2,
            ),
            array(
                'condition' => array('userId' => 1),
                'expectedResults' => array($factor[0], $factor[2]),
                'expectedCount' => 2,
            ),
            array(
                'condition' => array('type' => 'course'),
                'expectedResults' => $factor,
                'expectedCount' => 3,
            ),
            array(
                'condition' => array('courseSetId' => 1),
                'expectedResults' => array($factor[0], $factor[1]),
                'expectedCount' => 2,
            ),
            array(
                'condition' => array('courseSetIds' => array(1, 2)),
                'expectedResults' => $factor,
                'expectedCount' => 3,
            ),
            array(
                'condition' => array('excludeCourseIds' => array(1)),
                'expectedResults' => array($factor[2]),
                'expectedCount' => 1,
            ),
        );
    }

    public function testGetByUserIdAndCourseSetId()
    {
        $factor = $this->mockDataObject();

        $res = $this->getDao()->getByUserIdAndCourseSetId(1, 1);

        $this->assertEquals($factor, $res);
    }

    public function testCountByUserId()
    {
        $factor = array();
        $factor[] = $this->mockDataObject();
        $factor[] = $this->mockDataObject(array('userId' => 2));
        $factor[] = $this->mockDataObject(array('courseId' => 2));

        $res = $this->getDao()->countByUserId(1);

        $this->assertEquals(2, $res);
    }
    
    protected function getDefaultMockFields()
    {
        return array(
            'courseId' => 1,
            'userId' => 1,
            'type' => 'course',
            'courseSetId' => 1
        );
    }
}
