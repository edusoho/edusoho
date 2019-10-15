<?php

namespace Tests\Unit\Testpaper\Dao;

use Tests\Unit\Base\BaseDaoTestCase;

class TestpaperDaoTest extends BaseDaoTestCase
{
    public function testFindTestpapersByCopyIdAndCourseSetIds()
    {
        $this->mockDataObject();
        $results = $this->getDao()->findTestpapersByCopyIdAndCourseSetIds(1, array(1));
        $this->assertEquals('test', $results[0]['name']);
    }

    public function testGetItemsCountByParams()
    {
        $this->mockDataObject();
        $this->getDao()->deleteByCourseSetId(1);
        $result = $this->getDao()->count(array());
        $this->assertEquals(0, $result);
    }

    public function getDefaultMockFields()
    {
        return array(
            'name' => 'test',
            'description' => 1,
            'courseId' => 0,
            'lessonId' => '1',
            'limitedTime' => 1,
            'pattern' => '',
            'target' => '',
            'status' => 'draft',
            'score' => 2.00,
            'passedCondition' => '',
            'itemCount' => 3,
            'courseSetId' => 1,
            'copyId' => 1,
        );
    }
}
