<?php

namespace Topxia\DataTag\Test;

use Topxia\Service\Common\BaseTestCase;
use Topxia\DataTag\EliteCourseThreadsByTypeDataTag;

class EliteCourseThreadsByTypeDataTagTest extends BaseTestCase
{   

    public function testGetData()
    {
        $datatag = new EliteCourseThreadsByTypeDataTag();
        $datatag->getData(array('count' => 5));

    }

}