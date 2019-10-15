<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use Biz\BaseTestCase;
use AppBundle\Extensions\DataTag\PopularCoursesByCategoryDataTag;

class PopularCoursesByCategoryDataTagTest extends BaseTestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testEmptyCount()
    {
        $dataTag = new PopularCoursesByCategoryDataTag();
        $dataTag->getData(array());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testMaxCount()
    {
        $dataTag = new PopularCoursesByCategoryDataTag();
        $dataTag->getData(array('count' => 101));
    }

    public function testGetData()
    {
        $dataTag = new PopularCoursesByCategoryDataTag();

        $this->mockBiz('Course:CourseService', array(
            array(
                'functionName' => 'searchCourses',
                'returnValue' => array(),
            ),
        ));

        $course = $dataTag->getData(array('categoryId' => 1, 'count' => 10));
        $this->assertEmpty($course);
    }
}
