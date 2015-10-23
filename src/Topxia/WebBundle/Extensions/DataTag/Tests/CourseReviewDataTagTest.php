<?php

namespace Topxia\WebBundle\Extensions\DataTag\Test;

use Topxia\Service\Common\BaseTestCase;
use Topxia\WebBundle\Extensions\DataTag\CourseReviewDataTag;

class CourseReviewDataTagTest extends BaseTestCase
{   

    public function testGetData()
    {
        $datatag = new CourseReviewDataTag();
        $review = $datatag->getData(array('courseId' => 1, 'reviewId' => 1));
        $this->assertEquals(0, count($review));
    }

}