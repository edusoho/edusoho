<?php

namespace Topxia\DataTag\Test;

use Topxia\Service\Common\BaseTestCase;
use Topxia\DataTag\CoursesCountDataTag;

class CoursesCountDataTagTest extends BaseTestCase
{   

    public function testGetData()
    {
        $datatag = new CoursesCountDataTag();
        $datatag->getData(array());

    }

}