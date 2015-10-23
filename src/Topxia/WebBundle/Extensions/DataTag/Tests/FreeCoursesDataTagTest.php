<?php

namespace Topxia\WebBundle\Extensions\DataTag\Test;

use Topxia\Service\Common\BaseTestCase;
use Topxia\WebBundle\Extensions\DataTag\FreeCoursesDataTag;

class FreeCoursesDataTagTest extends BaseTestCase
{   

    public function testGetData()
    {
        $datatag = new FreeCoursesDataTag();
        $datatag->getData(array('count' => 5));

    }

}