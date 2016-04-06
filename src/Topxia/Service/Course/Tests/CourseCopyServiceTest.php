<?php
namespace Topxia\Service\Course\Tests;

use Topxia\Service\User\CurrentUser;
use Topxia\Service\Common\BaseTestCase;

class CourseCopyServiceTest extends BaseTestCase
{

	public function testSimpleCopy()
	{
        $course1 = $this->getCourseService()->createCourse(array('title' => 'test course 1'));
        $copyCourse = $this->getCourseCopyService()->copy($course1);
        $this->assertEquals($copyCourse['title'],$course1['title']);
	}

	public function testCopyTeachers()
	{
		 $course1 = $this->getCourseService()->createCourse(array('title' => 'test course 1','userId'=>1));
		 $copyCourse = $this->getCourseCopyService()->copy($course1);
		 $copyCourse = $this->getCourseService()->getCourse($copyCourse['id']);
		 
		 $this->assertEquals(count($copyCourse['teacherIds']),count($course1['teacherIds']));

		 foreach ($course1['teacherIds'] as $teacherId) {
		 	$this->assertTrue(in_array($teacherId, $copyCourse['teacherIds'], true));
		 }

	}


	protected function getCourseCopyService()
    {
        return $this->getServiceKernel()->createService('Course.CourseCopyService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

}
