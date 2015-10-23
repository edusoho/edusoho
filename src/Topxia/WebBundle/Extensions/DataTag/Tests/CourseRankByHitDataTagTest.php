<?php

namespace Topxia\WebBundle\Extensions\DataTag\Test;

use Topxia\Service\Common\BaseTestCase;
use Topxia\WebBundle\Extensions\DataTag\CourseRankByHitDataTag;

class CourseRankByHitDataTagTest extends BaseTestCase
{   

    public function testGetData()
    {
        $datatag = new CourseRankByHitDataTag();
        $courses = $datatag->getData(array('count' => 5));

        $this->assertEquals(0, count($courses));
    }

}