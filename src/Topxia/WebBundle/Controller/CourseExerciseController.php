<?php 
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Topxia\Common\ArrayToolkit;

class CourseExerciseController extends BaseController
{

	public function startDoAction(Request $Request,$courseId, $exerciseId)
	{
        list($course, $member) = $this->getCourseService()->tryTakeCourse($courseId);

        $exercise = $this->getExerciseService()->getExercise($exerciseId);
        if (empty($exercise)) {
            throw $this->createNotFoundException();
        }

        if ($exercise['courseId'] != $course['id']) {
            throw $this->createNotFoundException();
        }

        $lesson = $this->getCourseService()->getCourseLesson($exercise['courseId'], $exercise['lessonId']);
        if (empty($lesson)) {
            return $this->createMessageResponse('info','作业所属课时不存在！');
        }

        $result = $this->getExerciseService()->startExercise($exerciseId);

        var_dump($result);exit();
        return $this->redirect($this->generateUrl('course_exercise_do', 
            array(
                'courseId' => $result['courseId'],
                'exerciseId' => $result['exerciseId'],
                'resultId' => $result['id'],
            ))
        );
	}

	public function doAction(Request $Request,$courseId,$lessonId)
	{
        $exercise = $this->getExerciseService()->getExerciseByCourseIdAndLessonId($courseId, $lessonId);

        if (empty($exercise['itemCount']) && empty($exercise['questionTypeRange'])) {
        	throw $this->createNotFoundException();
        }

        list($course, $member) = $this->getCourseService()->tryTakeCourse($exercise['courseId']);

        if ($exercise['courseId'] != $course['id']) {
            throw $this->createNotFoundException();
        }

        $lesson = $this->getCourseService()->getCourseLesson($exercise['courseId'], $exercise['lessonId']);
        
        if (empty($lesson)) {
            return $this->createMessageResponse('info','练习所属课时不存在！');
        }


        $questionTypeRange = $exercise['questionTypeRange'];
        $questionTypeRange = $this->getquestionTypeRangeStr($questionTypeRange);

        $questions = $this->getQuestionService()->findQuestionsbyTypeRange($questionTypeRange,0,$exercise['itemCount']);
        $itemSet = $this->getItemSet($questions);
var_dump($itemSet);exit();
		return $this->render('TopxiaWebBundle:CourseExercise:do.html.twig', array(
            'homework' => $homework,
            'itemSet' => $itemSet,
            'course' => $course,
            'lesson' => $lesson,
            'questionStatus' => 'doing'
        ));
	}

	private function getquestionTypeRangeStr(array $questionTypeRange)
	{
        $questionTypeRangeStr = "";
		foreach ($questionTypeRange as $key => $questionType) {
			$questionTypeRangeStr .= "'{$questionType}',";
		}
        return substr($questionTypeRangeStr, 0,-1);
	}

	private function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
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