<?php

namespace Topxia\WebBundle\Extensions\DataTag\Test;

use Topxia\Service\Common\BaseTestCase;
use Topxia\WebBundle\Extensions\DataTag\ElitedCourseThreadsDataTag;

class ElitedCourseThreadsDataTagTest extends BaseTestCase
{   

    public function testGetData()
    {
        $datatag = new ElitedCourseThreadsDataTag();
        $datatag->getData(array('courseId' => 1, 'count' => 5));

    }

}