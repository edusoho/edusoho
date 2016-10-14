<?php

namespace Activity\Service\Activity\Tests;

use Topxia\Service\Common\BaseTestCase;

class ActivityServiceTest extends BaseTestCase
{
    public function testCreateActivity()
    {
        $activity = array(
            'title' => 'test activity 1'
        );
        $createCourse = $this->getCourseService()->createCourse($course);
        $result       = $this->getCourseService()->getCourse($createCourse['id']);
        $this->assertEquals($course['title'], $result['title']);
    }
}
