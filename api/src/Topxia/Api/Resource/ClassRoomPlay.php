<?php

namespace Topxia\Api\Resource;

use Silex\Application;
use Topxia\Common\ArrayToolkit;
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
			array('startTime', 'ASC'), 
			0, $searchLessonCount
		);

		return $this->createPlayArray($courses, $lessons);
	}

	protected function createPlayArray($courses, $lessons) {
		$playArray = array();
		
		$lastCourseId = 0;
		foreach ($lessons as $key => $lesson) {
			$currentCourseId = $lesson['courseId'];
			if ($lastCourseId != $currentCourseId) {
				$playArray[] = $this->findLabelInArrayById($currentCourseId, $courses);
				$lastCourseId = $currentCourseId;
			}
			$playArray[] = $lesson;
		}
		return $playArray;
	}

	protected function findLabelInArrayById($courseId, $courses) {
		foreach ($courses as $key => $course) {
			if ($courseId == $course['id']) {
				return array(
					"id" => $courseId,
					"title" => $course['title'],
					"type" => "label"
				);
			}
		}

		return null;
	}

	public function filter($res) {
		return $res;
	}

	protected function findCoursesByClassRoomId($classRoomId) {
		return $this->getClassroomService()->findCoursesByClassroomId($classRoomId);
	}

	protected function getClassroomService()
    {
        return $this->getServiceKernel()->createService('Classroom:Classroom.ClassroomService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }
}