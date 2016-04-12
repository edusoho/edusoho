<?php

namespace Topxia\Api\Resource;

use Silex\Application;
use Topxia\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;

class ClassRoomPlayStatus extends BaseResource
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

		$lessonStatusArray = $this->getClassRoomPlayStatus();
		$lessonStatusArray = array_map(function($lessonStatus) {
			if (in_array($lessonStatus['courseId'], $courseIds)) {
				return $lessonStatus;
			}

		}, $lessonStatusArray);

		return $lessonStatusArray;
	}

	protected function getClassRoomPlayStatus() {
		$user = $this->getCurrentUser();
		if (empty($user)) {
			return array();
		}

		$lessonStatusCount = $this->getCourseService()->searchLearnCount(
			array("userId"=>$user['id'])
		);
		$lessonStatusArray = $this->getCourseService()->searchLearns(
			array("userId"=>$user['id']),
			array('startTime', 'ASC'),
			0,
			$lessonStatusCount
		);

		return $lessonStatusArray;
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