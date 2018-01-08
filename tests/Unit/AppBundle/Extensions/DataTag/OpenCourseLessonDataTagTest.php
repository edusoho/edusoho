<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use Biz\BaseTestCase;
use AppBundle\Extensions\DataTag\OpenCourseLessonDataTag;

class OpenCourseLessonDataTagTest extends BaseTestCase
{
    public function testGetData()
    {
        $this->mockBiz('OpenCourse:OpenCourseService', array(
            array(
                'functionName' => 'getLesson',
                'returnValue' => array('id' => 1, 'title' => 'open course lesson')
            )
        ));

        $dataTag = new OpenCourseLessonDataTag();
        $data = $dataTag->getData(array('lessonId' => 1));
        $this->assertEquals(1, $data['id']);
    }

}
