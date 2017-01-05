<?php
namespace AppBundle\Controller\Course;

class CourseShowMetas
{
	public static function getMemberCourseShowMetas()
	{
		return array(
			'header' => 'AppBundle:My/Course:headerForMember',
			'tabs'	=> array(
				'courseSummary' => array(
					'name' => '课程介绍', 
					'path' => 'course_show'
				),
				'courseSummary' => array(
					'name' => '课程介绍', 
					'path' => 'course_show'
				)
			),
			'widgets' => CourseShowMetas::getGuestCourseShowMetas(),
		);
	}

	public static function getGuestCourseShowMetas()
	{
		return array(
			'header' => 'AppBundle:Course:header',
			'tabs'	=> array(
				'courseSummary' => array(
					'name' => '课程介绍', 
					'path' => 'course_show'
				),
				'courseItems' => array(
					'name' => '课程目录', 
					'path' => 'course_task_list'
				),
				'courseThreads' => array(
					'name' 		=> '话题', 
					'path' 		=> 'course_threads',
					'number'	=> 'threadNum'
				),
				'courseReviews' => array(
					'name' => '评价', 
					'path' => 'course_review',
					'number'	=> 'reviewNum'
				),
				'courseNotes' => array(
					'name' => '笔记',
					'path' => 'course_notes',
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