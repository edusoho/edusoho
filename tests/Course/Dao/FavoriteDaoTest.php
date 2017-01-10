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

    // Todo
    // 覆盖searchByUserId
    public function testSearch()
    {
        $factor = array();
        $factor[] = $this->mockDataObject();
        $factor[] = $this->mockDataObject(array('userId' => 2));
        $factor[] = $this->mockDataObject(array('courseId' => 2));
        
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
