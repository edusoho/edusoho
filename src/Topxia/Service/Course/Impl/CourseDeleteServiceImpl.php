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
    	    	
    	$this->getCourseDao()->getConnection()->beginTransaction();
    	try{
    		$course = $this->getCourseService()->getCourse($courseId);
    	} catch (\Exception $e) {

    		$this->getCourseDao()->getConnection()->rollback();
            
            throw $e;
    	}
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