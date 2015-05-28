<?php

namespace Topxia\DataTag\Test;

use Topxia\Service\Common\BaseTestCase;
use Topxia\DataTag\CourseThreadDataTag;

class CourseThreadDataTagTest extends BaseTestCase
{   

    public function testGetData()
    {
        $datatag = new CourseThreadDataTag();
        $threads = $datatag->getData(array('courseId' => 1, 'threadId' => 1));
        $this->assertEquals(0, count($threads));
    }

}