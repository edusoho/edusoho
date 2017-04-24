<?php

namespace AppBundle\Extensions\DataTag\Test;

use Biz\BaseTestCase;
use AppBundle\Extensions\DataTag\CourseThreadsByTypeDataTag;

class CourseThreadsByTypeDataTagTest extends BaseTestCase
{
    public function testGetData()
    {
        $datatag = new CourseThreadsByTypeDataTag();
        $datatag->getData(array('type' => 'question', 'count' => 5));
    }
}
