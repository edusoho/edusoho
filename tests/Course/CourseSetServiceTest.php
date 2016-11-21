<?php

namespace Tests;

use Topxia\Service\Common\BaseTestCase;

class CourseSetServiceTest extends BaseTestCase
{
    public function testCreate()
    {
        //TODO
    }

    protected function getCourseSetService()
    {
        return $this->getBiz()->service('Course:CourseSetService');
    }
}
