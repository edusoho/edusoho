<?php

namespace Topxia\DataTag\Test;

use Topxia\Service\Common\BaseTestCase;
use Topxia\DataTag\PopularCoursesByCategoryDataTag;

class PopularCoursesByCategoryDataTagTest extends BaseTestCase
{   

    public function testGetData()
    {
        $datatag = new PopularCoursesByCategoryDataTag();
        $datatag->getData(array('categoryId' => 1, 'count' => 5));

    }

}