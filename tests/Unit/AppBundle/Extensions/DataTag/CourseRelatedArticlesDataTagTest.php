<?php

namespace Tests\Unit\AppBundle\Extensions\DataTag;

use Biz\BaseTestCase;
use AppBundle\Extensions\DataTag\CourseRelatedArticlesDataTag;

class CourseRelatedArticlesDataTagTest extends BaseTestCase
{
    public function testGetData()
    {
        $datatag = new CourseRelatedArticlesDataTag();
        $articles = $datatag->getData(array('courseId' => 1));
        $this->assertEquals(0, count($articles));
    }
}
