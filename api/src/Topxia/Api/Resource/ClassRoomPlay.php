<?php

namespace Topxia\Api\Resource;

use Silex\Application;
use AppBundle\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;

class ClassRoomPlay extends BaseResource
{
	public function get(Application $app, Request $request, $classRoomId) 
	{
		if (empty($classRoomId)) {
			return array();
		}
		$courses = $this->findCoursesByClassroomId($classRoomId);

		if (empty($courses)) {
			return array();
		}

		$courseIds = ArrayToolkit::column($courses, "id");
		if (empty($courseIds)) {
			return array();
		}

		$searchLessonCount = $this->getCourseService()->searchLessonCount(array("courseIds"=>$courseIds));
		$lessons = $this->getCourseService()->searchLessons(
			array("courseIds"=>$courseIds), 
			array('startTime'=> 'ASC'), 
			0, $searchLessonCount
		);

		return $this->createPlayArray($courses, $lessons);
	}

	protected function createPlayArray($courses, $lessons) {		
		$courseLabels = $this->getCourseLabelArrayById($courses);
		foreach ($lessons as $key => $lesson) {
			$courseId = $lesson['courseId'];
			if (isset($courseLabels[$courseId])) {
				$courseLabels[$courseId][] = $lesson;
			}
		}
		$playArray = array();
		foreach ($courseLabels as $key => $value) {
			$playArray = array_merge($playArray, $value);
		}
		return $playArray;
	}

	protected function getCourseLabelArrayById($courses) {
		$courseLabels = array();
		foreach ($courses as $key => $course) {
			$courseLabels[$course['id']][] = array(
				"id" => $course['id'],
				"title" => $course['title'],
				"type" => "label"
			);
		}

		return $courseLabels;
	}

	public function filter($res) {
		return $res;
	}

	protected function findCoursesByClassRoomId($classRoomId) {
		return $this->getClassroomService()->findCoursesByClassroomId($classRoomId);
	}

	protected function getClassroomService()
    {
        return $this->getServiceKernel()->createService('Classroom:ClassroomService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course:CourseService');
    }
}