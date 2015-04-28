<?php

namespace Topxia\DataTag\Test;

use Topxia\Service\Common\BaseTestCase;
use Topxia\DataTag\PopularCoursesDataTag;

class PopularCoursesDataTagTest extends BaseTestCase
{   

    public function testGetData()
    {
        $datatag = new PopularCoursesDataTag();
        $datatag->getData(array('type' => 'hitNum', 'count' => 5));

    }

}