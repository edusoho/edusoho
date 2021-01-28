<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use Biz\BaseTestCase;
use AppBundle\Extensions\DataTag\OpenCourseLessonsDataTag;

class OpenCourseLessonsDataTagTest extends BaseTestCase
{
    public function testGetData()
    {
        $this->mockBiz('OpenCourse:OpenCourseService', array(
            array(
                'functionName' => 'searchLessons',
                'returnValue' => array(array('id' => 1, 'title' => 'open course lesson', 'courseId' => 1)),
            ),
        ));

        $dataTag = new OpenCourseLessonsDataTag();
        $data = $dataTag->getData(array('courseId' => 1, 'count' => 1));
        $this->assertEquals(1, $data[0]['id']);
    }
}
