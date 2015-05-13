<?php

namespace Topxia\DataTag\Test;

use Topxia\Service\Common\BaseTestCase;
use Topxia\DataTag\FreeCoursesDataTag;

class FreeCoursesDataTagTest extends BaseTestCase
{   

    public function testGetData()
    {
        $datatag = new FreeCoursesDataTag();
        $datatag->getData(array('count' => 5));

    }

}