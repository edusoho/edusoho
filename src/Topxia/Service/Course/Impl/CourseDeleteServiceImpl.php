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

            $deleteQuestions = $this->deleteQuestions($course);
            
            $this->getCourseDao()->getConnection()->commit();

    	} catch (\Exception $e) {

    		$this->getCourseDao()->getConnection()->rollback();
            
            throw $e;
    	}
    }

    protected function deleteQuestions($course)
    {
        $questions = $this->getQuestionDao()->findQuestionsByCourseId($course['id']);
        foreach ($questions as $question) {
            $this->getQuestionDao()->deleteQuestion($question['id']);
            $stem = strip_tags($question['stem']);
            $questionLog = "删除课程《{$course['title']}》(#{$course['id']})的问题　{$stem}";
            $this->getLogService()->info('question', 'delete', $questionLog);
        }

        //$questionsFavorite = $this->getQuestionFavoriteDao()->
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

    protected function getQuestionFavoriteDao()
    {
        return $this->createDao('Question.QuestionFavoriteDao');
    }

    protected function getCourseDao()
    {
        return $this->createDao('Course.CourseDao');
    }

    protected function getAppService()
    {
        return $this->createService('CloudPlatform.AppService');
    }

    protected function getLogService()
    {
        return $this->createService('System.LogService');
    }
}