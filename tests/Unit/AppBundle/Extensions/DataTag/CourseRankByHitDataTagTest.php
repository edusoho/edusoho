<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use Biz\BaseTestCase;
use AppBundle\Extensions\DataTag\CourseRankByHitDataTag;

class CourseRankByHitDataTagTest extends BaseTestCase
{
    public function testGetData()
    {
        $datatag = new CourseRankByHitDataTag();
        $courses = $datatag->getData(array('count' => 5));

        $this->assertEquals(0, count($courses));
    }
}
