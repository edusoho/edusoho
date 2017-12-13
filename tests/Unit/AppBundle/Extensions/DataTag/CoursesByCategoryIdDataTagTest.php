<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use Biz\BaseTestCase;
use AppBundle\Extensions\DataTag\CoursesByCategoryIdDataTag;

class CoursesByCategoryIdDataTagTest extends BaseTestCase
{
    public function testGetData()
    {
        $datatag = new CoursesByCategoryIdDataTag();
        // $datatag->getData(array('categoryId' => 1, 'count' => 5));
        $this->assertTrue(true);
    }
}
