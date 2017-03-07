<?php

namespace AppBundle\Extensions\DataTag\Test;

use Biz\BaseTestCase;
use AppBundle\Extensions\DataTag\CoursesCountDataTag;

class CoursesCountDataTagTest extends BaseTestCase
{
    public function testGetData()
    {
        $datatag = new CoursesCountDataTag();
        $datatag->getData(array());
    }
}
