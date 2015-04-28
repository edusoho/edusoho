<?php

namespace Topxia\DataTag\Test;

use Topxia\Service\Common\BaseTestCase;
use Topxia\DataTag\LatestCourseThreadsDataTag;

class LatestCourseThreadsDataTagTest extends BaseTestCase
{   

    public function testGetData()
    {
        $datatag = new LatestCourseThreadsDataTag();
        $datatag->getData(array('courseId' => 1, 'count' => 5));

    }

}