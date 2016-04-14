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


	public function testCopyChapters()
	{
		$course1 = $this->getCourseService()->createCourse(array('title' => 'test course 1','userId'=>1));
		$chapter1 = array('courseId' => $course1['id'], 'title' => 'chapter 1', 'type' => 'chapter', 'number' => '1', 'seq' => '1');
		$createdChapter1 = $this->getCourseService()->createChapter($chapter1);
		$copyCourse = $this->getCourseCopyService()->copy($course1);

		$copyChapters = $this->getCourseService()->getCourseChapters($copyCourse['id']);

		$this->assertEquals(1,count($copyChapters));
		$this->assertEquals($createdChapter1['title'],$copyChapters[0]['title']);
		$this->assertEquals($createdChapter1['type'],$copyChapters[0]['type']);
		$this->assertEquals($createdChapter1['number'],$copyChapters[0]['number']);
		$this->assertEquals($createdChapter1['seq'],$copyChapters[0]['seq']);
	}


	public function testCopyLessons()
	{
		$course1 = $this->getCourseService()->createCourse(array('title' => 'test course 1','userId'=>1));
		
		$lesson1       = array(
            'courseId'    => $course1['id'],
            'chapterId'   => 0,
            'free'        => 0,
            'title'       => 'test lesson 1',
            'number'      => '1',
            'summary'     => '',
            'type'        => 'text',
            'seq'         => '1',
            'parentId'    => 1,
            'userId'      => 1,
            'createdTime' => time()
        );
        $lesson2 = array(
            'courseId'    => $course1['id'],
            'chapterId'   => 0,
            'free'        => 0,
            'title'       => 'test lesson 2',
            'number'      => '2',
            'summary'     => '',
            'type'        => 'text',
            'seq'         => '2',
            'parentId'    => 1,
            'userId'      => 1,
            'createdTime' => time()
        );
        $createdLesson1 = $this->getCourseService()->createLesson($lesson1);
        $createdLesson2 = $this->getCourseService()->createLesson($lesson2);

		$copyCourse = $this->getCourseCopyService()->copy($course1);

		$lessons = $this->getCourseService()->getCourseLessons($copyCourse['id']);

		$this->assertEquals(2,count($lessons));
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
