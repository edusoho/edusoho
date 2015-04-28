<?php

namespace Topxia\DataTag\Test;

use Topxia\Service\Common\BaseTestCase;
use Topxia\DataTag\LatestCourseReviewsDataTag;

class LatestCourseReviewsDataTagTest extends BaseTestCase
{   

    public function testGetData()
    {
        $datatag = new LatestCourseReviewsDataTag();
        $datatag->getData(array('courseId' => 1, 'count' => 5));

    }

}