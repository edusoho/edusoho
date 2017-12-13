<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use Biz\BaseTestCase;
use AppBundle\Extensions\DataTag\EliteCourseThreadsByTypeDataTag;

class EliteCourseThreadsByTypeDataTagTest extends BaseTestCase
{
    public function testGetData()
    {
        $datatag = new EliteCourseThreadsByTypeDataTag();
        $datatag->getData(array('count' => 5));
    }
}
