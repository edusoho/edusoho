<?php

namespace Topxia\DataTag\Test;

use Topxia\Service\Common\BaseTestCase;
use Topxia\DataTag\ElitedCourseThreadsDataTag;

class ElitedCourseThreadsDataTagTest extends BaseTestCase
{   

    public function testGetData()
    {
        $datatag = new ElitedCourseThreadsDataTag();
        $datatag->getData(array('courseId' => 1, 'count' => 5));

    }

}