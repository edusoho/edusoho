<?php

namespace Topxia\DataTag\Test;

use Topxia\Service\Common\BaseTestCase;
use Topxia\DataTag\LatestCourseThreadsByTypeDataTag;

class LatestCourseThreadsByTypeDataTagTest extends BaseTestCase
{   

    public function testGetData()
    {
        $datatag = new LatestCourseThreadsByTypeDataTag();
        $datatag->getData(array('type' => 'question', 'count' => 5));

    }

}