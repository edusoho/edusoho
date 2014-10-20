<?php 
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CourseExerciseController extends BaseController
{
	public function doAction(Request $Request,$courseId,$lessonId)
	{
        $exercise = $this->getExerciseService()->getExerciseByCourseIdAndLessonId($courseId, $lessonId);
        $questions = $this->getQuestionService()->getQuestionsbyTypeRange($exercise['questionTypeRange']);
        var_dump($exercise);exit();
	}

	private function getExerciseService()
	{
        return $this->getServiceKernel()->createService('Course.ExerciseService');
	}

	private function getQuestionService()
    {
        return $this->getServiceKernel()->createService('Question.QuestionService');
    }
}