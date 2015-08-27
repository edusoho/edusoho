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

            $deleteQuestions = $this->deleteQuestions($course['id']);
            
            $this->getCourseDao()->getConnection()->commit();

    	} catch (\Exception $e) {

    		$this->getCourseDao()->getConnection()->rollback();
            
            throw $e;
    	}
    }

    protected function deleteQuestions($courseId)
    {
        $this->getQuestionDao()->deleteQuestionsByCourseId();
    }

    protected function getCourseService()
    {
        return $this->createService('Course.CourseService');
    }

    protected function getClassroomDao()
    {
        return $this->createDao('Classroom:Classroom.ClassroomDao');
    }

    protected function getQuestionDao()
    {
        return $this->createDao('Question.QuestionDao');
    }

    protected function getCourseDao()
    {
        return $this->createDao('Course.CourseDao');
    }

    protected function getAppService()
    {
        return $this->createService('CloudPlatform.AppService');
    }
}