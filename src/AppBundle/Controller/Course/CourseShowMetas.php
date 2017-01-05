<?php
namespace AppBundle\Controller\Course;

class CourseShowMetas
{
	public static function getMemberCourseShowMetas()
	{
		$metas = CourseShowMetas::getGuestCourseShowMetas();
		return array(
			'header' => 'AppBundle:My/Course:headerForMember',
			'tabs'	=> array(
				'tasks' => array(
					'name' => '课程目录', 
					'content'	=> 'AppBundle:Course:taskList'
				),
				'threads' => array(
					'name' 		=> '话题', 
					'number'	=> 'threadNum',
					'content'	=> 'AppBundle:CourseThread:index'
				),
				'reviews' => array(
					'name' => '评价', 
					'number'	=> 'reviewNum',
					'content'	=> 'AppBundle:Course:reviewList'
				),
				'notes' => array(
					'name' => '笔记',
					'number'	=> 'noteNum',
					'content'  => 'AppBundle:Course:notes'
				),
				'summary' => array(
					'name' => '课程介绍', 
					'content' => 'AppBundle:Course:summary'
				),
			),
			'widgets' => $metas['widgets'],
		);
	}

	public static function getGuestCourseShowMetas()
	{
		return array(
			'header' => 'AppBundle:Course:header',
			'tabs'	=> array(
				'summary' => array(
					'name' => '课程介绍', 
					'content' => 'AppBundle:Course:summary'
				),
				'tasks' => array(
					'name' => '课程目录', 
					'content'	=> 'AppBundle:Course:taskList'
				),
				'threads' => array(
					'name' 		=> '话题', 
					'number'	=> 'threadNum',
					'content'	=> 'AppBundle:CourseThread:index'
				),
				'reviews' => array(
					'name' => '评价', 
					'number'	=> 'reviewNum',
					'content'	=> 'AppBundle:Course:reviewList'
				),
				'notes' => array(
					'name' => '笔记',
					'number'	=> 'noteNum',
					'content'  => 'AppBundle:Course:notes'
				),
			),
			'widgets' => array(
				'characteristic' => array(
					'uri' => 'AppBundle:Course:characteristicPart',
					'type' => 'render'
				),
				'otherCourse' => array(
					'uri' => 'AppBundle:Course:otherCoursePart',
					'type' => 'render'
				),
				'recommendClassroom' => array(
					'uri' => 'course/part/recommend-classroom.html.twig',
					'type' => 'include'
				),
        		'teachers' => array(
        			'uri' => 'AppBundle:Course:teachersPart',
					'type' => 'render'
        		),
				'newestStudents' => array(
					'uri' => 'AppBundle:Course:newestStudentsPart',
					'type' => 'render'
				),
				'studentActivity' => array(
					'uri' => 'course/part/student-activity.html.twig',
					'type' => 'include'
				)
			),
		);
	}
}