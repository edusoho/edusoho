<?php

namespace Topxia\WebBundle\Extensions\DataTag\Test;

use Topxia\Service\Common\BaseTestCase;
use Topxia\WebBundle\Extensions\DataTag\CourseThreadsByTypeDataTag;

class CourseThreadsByTypeDataTagTest extends BaseTestCase
{   

    public function testGetData()
    {
        $datatag = new CourseThreadsByTypeDataTag();
        $datatag->getData(array('type' => 'question', 'count' => 5));

    }

}