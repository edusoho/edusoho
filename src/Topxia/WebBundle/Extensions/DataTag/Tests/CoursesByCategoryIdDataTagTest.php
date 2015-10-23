<?php

namespace Topxia\WebBundle\Extensions\DataTag\Test;

use Topxia\Service\Common\BaseTestCase;
use Topxia\WebBundle\Extensions\DataTag\CoursesByCategoryIdDataTag;

class CoursesByCategoryIdDataTagTest extends BaseTestCase
{   

    public function testGetData()
    {
        $datatag = new CoursesByCategoryIdDataTag();
        $datatag->getData(array('categoryId' => 1, 'count' => 5));

    }

}