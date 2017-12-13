<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use Biz\BaseTestCase;
use AppBundle\Extensions\DataTag\ElitedCourseThreadsDataTag;

class ElitedCourseThreadsDataTagTest extends BaseTestCase
{
    public function testGetData()
    {
        $datatag = new ElitedCourseThreadsDataTag();
        $datatag->getData(array('courseId' => 1, 'count' => 5));
    }
}
