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
					'content'	=> 'AppBundle:Course:tasks'
				),
				'threads' => array(
					'name' 		=> '话题', 
					'number'	=> 'threadNum',
					'content'	=> 'AppBundle:CourseThread:index'
				),
				'reviews' => array(
					'name' => '评价', 
					'number'	=> 'reviewNum',
					'content'	=> 'AppBundle:Course:reviews'
				),
				'notes' => array(
					'name' => '笔记',
					'number'	=> 'noteNum',
					'content'  => 'AppBundle:Course:notes'
				),
				'material' => array(
					'name' => '资料区',
					'number'	=> 'materialNum',
					'content'  => 'AppBundle:Course/CourseMaterial:index'
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
					'content'	=> 'AppBundle:Course:tasks'
				),
				'reviews' => array(
					'name' => '评价', 
					'number'	=> 'reviewNum',
					'content'	=> 'AppBundle:Course:reviews'
				),
				'notes' => array(
					'name' => '笔记',
					'number'	=> 'noteNum',
					'content'  => 'AppBundle:Course:notes'
				)
			),
			'widgets' => array(
				'characteristic' => array(
					'uri' => 'AppBundle:Course:characteristic',
					'type' => 'render'
				),
				'otherCourse' => array(
					'uri' => 'AppBundle:Course:otherCourse',
					'type' => 'render'
				),
				'recommendClassroom' => array(
					'uri' => 'course/widgets/recommend-classroom.html.twig',
					'type' => 'include'
				),
        		'teachers' => array(
        			'uri' => 'AppBundle:Course:teachers',
					'type' => 'render'
        		),
				'newestStudents' => array(
					'uri' => 'AppBundle:Course:newestStudents',
					'type' => 'render'
				),
				'studentActivity' => array(
					'uri' => 'course/widgets/student-activity.html.twig',
					'type' => 'include'
				)
			),
		);
	}
}