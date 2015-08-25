<?php
namespace Topxia\Service\Course\Impl;

use Topxia\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\File\File;
use Topxia\Service\Common\BaseService;
use Topxia\Service\Course\CourseDeleteService;

class CourseDeleteServiceImpl extends BaseService implements CourseDeleteService
{
	public function delete($courseId)
    {
    	$course = $this->getCourseService()->getCourse($courseId);
    	$this->getClassroomDao()->getConnection()->beginTransaction();
    	try{
    		$this->removeClassroomCourse($course['id']);
    		$this->getClassroomDao()->getConnection()->commit();
    	} catch (\Exception $e) {
    		$this->getClassroomDao()->getConnection()->rollback();
            throw $e;
    	}
    	
    	$this->getCourseDao()->getConnection()->beginTransaction();
    	try{
    		
    	} catch (\Exception $e) {

    		$this->getCourseDao()->getConnection()->rollback();
            
            throw $e;
    	}
    }

    protected function removeClassroomCourse($courseId)
    {
    	$classroomIds = $this->getClassroomService()->findClassroomIdsByCourseId($courseId);
    	$classroom_courses = $this->getClassroomService()->findCoursesByClassroomId($classroomIds[0]);
    	$classroom_coursesIds = ArrayToolkit::column($classroom_courses,"id");
    	if (in_array($courseId, $classroom_coursesIds)) {
 			$key = array_search($courseId, $classroom_coursesIds);
 			unset($classroom_coursesIds[$key]);
    	}
    	$this->getClassroomService()->updateClassroomCourses($classroomIds[0],$classroom_coursesIds);
    }

    protected function getClassroomService()
    {
        return $this->createService('Classroom:Classroom.ClassroomService');
    }

    protected function getCourseService()
    {
        return $this->createService('Course.CourseService');
    }

    protected function getClassroomDao()
    {
        return $this->createDao('Classroom:Classroom.ClassroomDao');
    }

    protected function getCourseDao()
    {
        return $this->createDao('Course.CourseDao');
    }
}