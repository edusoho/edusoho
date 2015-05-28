<?php

namespace Topxia\DataTag\Test;

use Topxia\Service\Common\BaseTestCase;
use Topxia\DataTag\CourseRankByRatingDataTag;

class CourseRankByRatingDataTagTest extends BaseTestCase
{   

    public function testGetData()
    {
        $datatag = new CourseRankByRatingDataTag();
        $courses = $datatag->getData(array('count' => 5));

        $this->assertEquals(0, count($courses));
    }

}