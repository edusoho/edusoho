<?php

namespace Topxia\DataTag\Test;

use Topxia\Service\Common\BaseTestCase;
use Topxia\DataTag\CourseThreadsByTypeDataTag;

class CourseThreadsByTypeDataTagTest extends BaseTestCase
{   

    public function testGetData()
    {
        $datatag = new CourseThreadsByTypeDataTag();
        $datatag->getData(array('type' => 'question', 'count' => 5));

    }

}