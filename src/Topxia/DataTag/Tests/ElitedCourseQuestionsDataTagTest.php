<?php

namespace Topxia\DataTag\Test;

use Topxia\Service\Common\BaseTestCase;
use Topxia\DataTag\ElitedCourseQuestionsDataTag;

class ElitedCourseQuestionsDataTagTest extends BaseTestCase
{   

    public function testGetData()
    {
        $datatag = new ElitedCourseQuestionsDataTag();
        $datatag->getData(array('courseId' => 1, 'count' => 5));

    }

}