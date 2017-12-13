<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use Biz\BaseTestCase;
use AppBundle\Extensions\DataTag\ElitedCourseQuestionsDataTag;

class ElitedCourseQuestionsDataTagTest extends BaseTestCase
{
    public function testGetData()
    {
        $datatag = new ElitedCourseQuestionsDataTag();
        // $datatag->getData(array('courseId' => 1, 'count' => 5));
        $this->assertTrue(true);
    }
}
