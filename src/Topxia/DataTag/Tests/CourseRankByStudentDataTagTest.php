<?php

namespace Topxia\DataTag\Test;

use Topxia\Service\Common\BaseTestCase;
use Topxia\DataTag\CourseRankByStudentDataTag;

class CourseRankByStudentDataTagTest extends BaseTestCase
{   

    public function testGetData()
    {
        $datatag = new CourseRankByStudentDataTag();
        $courses = $datatag->getData(array('count' => 5));

        $this->assertEquals(0, count($courses));
    }

}