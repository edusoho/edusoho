<?php

namespace Topxia\Api\Resource;

use Silex\Application;
use AppBundle\Common\ArrayToolkit;
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

		$realLessonStatusArray = array();
		$lessonStatusArray = $this->getClassRoomPlayStatus();
		foreach ($lessonStatusArray as $key => $lessonStatus) {
			if (in_array($lessonStatus['courseId'], $courseIds)) {
				$realLessonStatusArray[] = $lessonStatus;
			}
		}

		return $realLessonStatusArray;
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
			array('startTime'=> 'ASC'),
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
        return $this->getServiceKernel()->createService('Classroom:ClassroomService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course:CourseService');
    }
}