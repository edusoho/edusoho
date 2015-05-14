<?php

namespace Topxia\DataTag\Test;

use Topxia\Service\Common\BaseTestCase;
use Topxia\DataTag\LatestCourseQuestionsDataTag;

class LatestCourseQuestionsDataTagTest extends BaseTestCase
{   

    public function testGetData()
    {
        $datatag = new LatestCourseQuestionsDataTag();
        $datatag->getData(array('courseId' => 1, 'count' => 5));

    }

}