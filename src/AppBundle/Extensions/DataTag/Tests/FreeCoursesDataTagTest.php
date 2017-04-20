<?php

namespace AppBundle\Extensions\DataTag\Test;

use Biz\BaseTestCase;
use AppBundle\Extensions\DataTag\FreeCoursesDataTag;

class FreeCoursesDataTagTest extends BaseTestCase
{
    public function testGetData()
    {
        $datatag = new FreeCoursesDataTag();
        $datatag->getData(array('count' => 5));
    }
}
