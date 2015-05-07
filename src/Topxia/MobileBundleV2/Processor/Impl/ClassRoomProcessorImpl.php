<?php
namespace Topxia\MobileBundleV2\Processor\Impl;

use Topxia\MobileBundleV2\Processor\BaseProcessor;
use Topxia\MobileBundleV2\Processor\ClassRoomProcessor;
use Topxia\Common\ArrayToolkit;

class ClassRoomProcessorImpl extends BaseProcessor implements ClassRoomProcessor
{
	public function after()
	{
		if (!class_exists('Classroom\Service\Classroom\Impl\ClassroomServiceImpl')) {
			$this->stopInvoke();
			return $this->createErrorResponse("no_classroom", "没有安装班级插件！");
		}
	}

	public function getClassRooms()
	{
		$start = (int) $this->getParam("start", 0);
        		$limit = (int) $this->getParam("limit", 10);
	        $conditions = array(
	            'status' => 'published'
	        );

	        $total = $this->getClassroomService()->searchClassroomsCount($conditions);

	        $classrooms = $this->getClassroomService()->searchClassrooms(
	                $conditions,
	                array('createdTime','desc'),
	                $start,
	                $total
	        );

	        $classRoomTeacherIds = ArrayToolkit::column($classrooms,'teacherIds');

	        for ($i=0; $i < count($classRoomTeacherIds); $i++) { 
	        	$teacherIds = $classRoomTeacherIds[$i];
	        	$users = $this->getUserService()->findUsersByIds($teacherIds);

	    	$classrooms[$i]["teacherIds"] = $this->filterUsersFiled($users);
	        }

	        return $classrooms;
	}

	    private function getClassroomService() 
	    {
	    	return $this->controller->getService('Classroom:Classroom.ClassroomService');
	    }

	    protected function getClassroomOrderService()
	    {
	        return $this->controller->getService('Classroom:Classroom.ClassroomOrderService'); 
	    }

	    protected function getClassroomReviewService()
	    {
	        return $this->controller->getService('Classroom:Classroom.ClassroomReviewService');
	    }
}