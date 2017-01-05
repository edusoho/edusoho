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
				'courseItems' => array(
					'name' => '课程目录', 
					'route' => 'course_task_list'
				),
				'courseThreads' => array(
					'name' 		=> '话题', 
					'route' 		=> 'course_threads',
					'number'	=> 'threadNum'
				),
				'courseReviews' => array(
					'name' => '评价', 
					'route' => 'course_review',
					'number'	=> 'reviewNum'
				),
				'courseNotes' => array(
					'name' => '笔记',
					'route' => 'course_notes',
					'number'	=> 'noteNum'
				),
				'courseSummary' => array(
					'name' => '课程介绍', 
					'route' => 'course_show'
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
				'courseSummary' => array(
					'name' => '课程介绍', 
					'route' => 'course_show'
				),
				'courseItems' => array(
					'name' => '课程目录', 
					'route' => 'course_task_list'
				),
				'courseThreads' => array(
					'name' 		=> '话题', 
					'route' 		=> 'course_threads',
					'number'	=> 'threadNum'
				),
				'courseReviews' => array(
					'name' => '评价', 
					'route' => 'course_review',
					'number'	=> 'reviewNum'
				),
				'courseNotes' => array(
					'name' => '笔记',
					'route' => 'course_notes',
					'number'	=> 'noteNum'
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