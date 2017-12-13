<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

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
