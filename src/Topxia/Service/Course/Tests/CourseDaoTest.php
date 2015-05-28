<?php
namespace Topxia\Service\Course\Tests;

use Topxia\Service\Common\BaseTestCase;
use Topxia\Common\ArrayToolkit;

class CourseDaoTest extends BaseTestCase
{

    public function testFindCoursesByTagIdsAndStatus()
    {
        $this->getCourseDao()->addCourse(array('title' => 'title1', 'status' => 'published', 'tags' => '|1|2|3|', 'userId' => 1, 'createdTime' => time()));
        $this->getCourseDao()->addCourse(array('title' => 'title1', 'status' => 'published', 'tags' => '|2|3|', 'userId' => 1, 'createdTime' => time()));
        $this->getCourseDao()->addCourse(array('title' => 'title1', 'status' => 'published', 'tags' => '|4|', 'userId' => 1, 'createdTime' => time()));
        $this->getCourseDao()->addCourse(array('title' => 'title1', 'status' => 'published', 'tags' => '|2|5|', 'userId' => 1, 'createdTime' => time()));
        $this->getCourseDao()->addCourse(array('title' => 'title1', 'status' => 'closed', 'tags' => '|3|4|', 'userId' => 1, 'createdTime' => time()));

        $foundCourses = $this->getCourseDao()->findCoursesByTagIdsAndStatus(array(2,3), 'published', 0, 10);
        $this->assertEquals(2, count($foundCourses));
        foreach ($foundCourses as $course) {
            $this->assertContains('|2|', $course['tags']);
            $this->assertContains('|3|', $course['tags']);
        }

    }

    private function getCourseDao()
    {
        return $this->getServiceKernel()->createDao('Course.CourseDao'); 
    }

}