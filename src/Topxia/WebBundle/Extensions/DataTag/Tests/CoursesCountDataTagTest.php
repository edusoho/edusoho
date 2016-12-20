<?php

namespace Topxia\WebBundle\Extensions\DataTag\Test;

use Biz\BaseTestCase;;
use Topxia\WebBundle\Extensions\DataTag\CoursesCountDataTag;

class CoursesCountDataTagTest extends BaseTestCase
{   

    public function testGetData()
    {
        $datatag = new CoursesCountDataTag();
        $datatag->getData(array());

    }

}