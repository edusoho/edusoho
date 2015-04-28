<?php

namespace Topxia\DataTag\Test;

use Topxia\Service\Common\BaseTestCase;
use Topxia\DataTag\RecommendCoursesDataTag;

class RecommendCoursesDataTagTest extends BaseTestCase
{   

    public function testGetData()
    {
        $datatag = new RecommendCoursesDataTag();
        $datatag->getData(array('count' => 5));

    }

}