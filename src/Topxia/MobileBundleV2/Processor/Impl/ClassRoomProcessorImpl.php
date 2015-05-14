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

	public function myClassRooms()
	{	
		$start  = (int) $this->getParam("start", 0);
        		$limit  = (int) $this->getParam("limit", 10);

		$user = $this->controller->getUserByToken($this->request);
       		 if (!$user->isLogin()) {
            		return $this->createErrorResponse('not_login', "您尚未登录，不能查看班级！");
        		}
	        $progresses = array();
	        $classrooms=array();

	        $studentClassrooms=$this->getClassroomService()->searchMembers(array('role'=>'student','userId'=>$user->id),array('createdTime','desc'),0,9999);
	        $auditorClassrooms=$this->getClassroomService()->searchMembers(array('role'=>'auditor','userId'=>$user->id),array('createdTime','desc'),0,9999);

	        $total  = 0;
	        $total += $this->getClassroomService()->searchMemberCount(array('role'=>'student','userId'=>$user->id),array('createdTime','desc'),0,9999);
	        $total += $this->getClassroomService()->searchMemberCount(array('role'=>'auditor','userId'=>$user->id),array('createdTime','desc'),0,9999);
	        
	        $classrooms=array_merge($studentClassrooms,$auditorClassrooms);

	        $classroomIds=ArrayToolkit::column($classrooms,'classroomId');

	        $classrooms=$this->getClassroomService()->findClassroomsByIds($classroomIds);

	        foreach ($classrooms as $key => $classroom) {
	            
	            $courses=$this->getClassroomService()->findCoursesByClassroomId($classroom['id']);
	            $coursesCount=count($courses);

	            $classrooms[$key]['coursesCount']=$coursesCount;
	            
	            $classroomId= array($classroom['id']);
	            $member=$this->getClassroomService()->findMembersByUserIdAndClassroomIds($user->id, $classroomId);
	            $time=time()-$member[$classroom['id']]['createdTime'];
	            $day=intval($time/(3600*24));

	            $classrooms[$key]['day']=$day;
	            $progresses[$classroom['id']] = $this->calculateUserLearnProgress($classroom, $user->id);
	        }

	        $classrooms = $this->filterMyClassRoom($classrooms,$progresses);
	        return array(
	        	"start"=>$start,
	        	"total"=>$total,
	        	"limit"=>$total,
	        	"data"=>array_values($classrooms)
	        	);
	}

	private function filterMyClassRoom($classrooms, $progresses)
	{
		return array_map(function($classroom) use($progresses) {
			$progresse = $progresses[$classroom["id"]];
			$classroom["percent"] = $progresse["percent"];
		           $classroom["number"] = $progresse["number"];
		           $classroom["total"] = $progresse["total"];

			unset($classroom["description"]);
			unset($classroom["about"]);
			unset($classroom["teacherIds"]);
			unset($classroom["service"]);
			return $classroom;
		}, $classrooms);
	}

	private function calculateUserLearnProgress($classroom, $userId)
	    {
	        $courses=$this->getClassroomService()->findCoursesByClassroomId($classroom['id']);
	        $courseIds = ArrayToolkit::column($courses,'id');
	        $findLearnedCourses = array();
	        foreach ($courseIds as $key => $value) {
	            $LearnedCourses=$this->getCourseService()->findLearnedCoursesByCourseIdAndUserId($value,$userId);
	            if (!empty($LearnedCourses)) {
	                $findLearnedCourses[] = $LearnedCourses;
	            }
	        }

	        $learnedCoursesCount = count($findLearnedCourses);
	        $coursesCount=count($courses);

	        if ($coursesCount == 0) {
	            return array('percent' => '0%', 'number' => 0, 'total' => 0);
	        }

	        $percent = intval($learnedCoursesCount / $coursesCount * 100) . '%';

	        return array (
	            'percent' => $percent,
	            'number' => $learnedCoursesCount,
	            'total' => $coursesCount
	        );
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
	                $limit
	        );

	        $classRoomTeacherIds = ArrayToolkit::column($classrooms,'teacherIds');

	        for ($i=0; $i < count($classRoomTeacherIds); $i++) { 
	        	$teacherIds = $classRoomTeacherIds[$i];
	        	$users = $this->getUserService()->findUsersByIds($teacherIds);

	    	$classrooms[$i]["teacherIds"] = $this->filterUsersFiled($users);
	        }

	        return array(
	            "start" => $start,
	            "limit" => $limit,
	            "total" => $total,
	            "data" => $classrooms
	        );
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