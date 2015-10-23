<?php

namespace Topxia\WebBundle\Extensions\DataTag\Test;

use Topxia\Service\Common\BaseTestCase;
use Topxia\WebBundle\Extensions\DataTag\CourseRelatedArticlesDataTag;

class CourseRelatedArticlesDataTagTest extends BaseTestCase
{   

    public function testGetData()
    {
        $datatag = new CourseRelatedArticlesDataTag();
        $articles = $datatag->getData(array('courseId' => 1));
        $this->assertEquals(0, count($articles));
    }

}