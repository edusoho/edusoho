<?php

namespace AppBundle\Extensions\DataTag\Test;

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
