<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use Biz\BaseTestCase;
use AppBundle\Extensions\DataTag\CourseThreadDataTag;

class CourseThreadDataTagTest extends BaseTestCase
{
    public function testGetData()
    {
        $datatag = new CourseThreadDataTag();
        $threads = $datatag->getData(array('courseId' => 1, 'threadId' => 1));
        $this->assertEquals(0, count($threads));
    }
}
