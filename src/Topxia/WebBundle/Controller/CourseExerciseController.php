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

        $questionTypeRange = $exercise['questionTypeRange'];
        $questionTypeRange = $this->getquestionTypeRangeStr($questionTypeRange);

        $itemCount = $exercise['itemCount'];
        $excludeIds = $this->getExcludeIds($questionTypeRange,$itemCount);
        $result = $this->getExerciseService()->startExercise($exerciseId,$excludeIds);

        return $this->redirect($this->generateUrl('course_exercise_do', 
            array(
                'courseId' => $result['courseId'],
                'exerciseId' => $result['exerciseId'],
                'resultId' => $result['id'],
            ))
        );
	}

    private function getExcludeIds($questionTypeRange,$itemCount)
    {
        $questionCount = $this->getQuestionService()->findQuestionsCountbyTypeRange($questionTypeRange);

        $start = rand(0,$questionCount-$itemCount);
        $questions = $this->getQuestionService()->findQuestionsbyTypeRange($questionTypeRange,$start,$itemCount);
        $excludeIds = ArrayToolkit::column($questions,'id');

        while ( count($excludeIds) < $itemCount) {
            $questions = $this->getQuestionService()->findQuestionsbyTypeRange($questionTypeRange,$start,1);
            $Ids = ArrayToolkit::column($questions,'id');
            $excludeIds = array_merge($excludeIds,$Ids);
            $excludeIds = array_unique($excludeIds);
        }
        return $excludeIds;
    }

	public function doAction(Request $Request,$courseId,$exerciseId,$resultId)
	{
        $exercise = $this->getExerciseService()->getExercise($exerciseId);
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

        $itemSet = $this->getExerciseService()->getItemSetByExerciseId($exercise['id']);
		return $this->render('TopxiaWebBundle:CourseExercise:do.html.twig', array(
            'exercise' => $exercise,
            'itemSet' => $itemSet,
            'itemCount' => count($itemSet['items']),
            'course' => $course,
            'lesson' => $lesson,
            'questionStatus' => 'doing',
            'questionFor' => 'exercise',
        ));
	}

    public function submitAction(Request $request,$courseId,$exerciseId)
    {
        if ($request->getMethod() == 'POST') {
            $data = $request->request->all();
            $data = !empty($data['data']) ? $data['data'] : array();
            $result = $this->getExerciseService()->submitExercise($exerciseId,$data);
            if (!empty($result) && !empty($result['lessonId'])) {
               return $this->createJsonResponse(
                    array(
                        'courseId' => $courseId,
                        'lessonId' => $result['lessonId'],
                        'exerciseId' => $exerciseId,
                        'resultId' => $result['id'],
                        'userId' => $this->getCurrentUser()->id
                        )
                );
            }
        }
    }

    public function resultAction(Request $request, $courseId, $exerciseId, $resultId ,$userId)
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

        $itemSetResult = $this->getExerciseService()->getItemSetResultByExerciseIdAndUserId($exercise['id'],$userId);
        return $this->render('TopxiaWebBundle:CourseExercise:result.html.twig', array(
            'exercise' => $exercise,
            'itemSetResult' => $itemSetResult,
            'course' => $course,
            'lesson' => $lesson,
            'questionStatus' => 'finished'
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