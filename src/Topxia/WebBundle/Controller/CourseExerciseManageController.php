<?php
namespace Topxia\WebBundle\Controller;

use Topxia\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Paginator;

class CourseExerciseManageController extends BaseController
{
	public function createExerciseAction(Request $request, $courseId, $lessonId)
	{   
		list($course, $lesson) = $this->getExerciseCourseAndLesson($courseId, $lessonId);
        if($request->getMethod() == 'POST') {
        	$fields = $this->generateExerciseFields($request->request->all(), $course, $lesson);
        	$exercise = $this->getExerciseService()->createExercise($fields);
        	return $this->createJsonResponse($this->generateUrl('course_manage_lesson', array('id' => $course['id'])));
        }

		return $this->render('TopxiaWebBundle:CourseExerciseManage:exercise.html.twig', array(
			'course' => $course,
			'lesson' => $lesson,
			'exercise' => array('id' => null)
		));
	}

	public function updateExerciseAction(Request $request, $courseId, $lessonId, $id)
	{
		list($course, $lesson) = $this->getExerciseCourseAndLesson($courseId, $lessonId);

        $exercise = $this->getExerciseService()->getExercise($id);
        if(empty($exercise)) {
        	throw $this->createNotFoundException("练习(#{$id})不存在！");
        }

        if($request->getMethod() == 'POST') {
        	$fields = $this->generateExerciseFields($request->request->all(), $course, $lesson);

        	$exercise = $this->getExerciseService()->updateExercise($exercise['id'], $fields);
        	return $this->createJsonResponse($this->generateUrl('course_manage_lesson', array('id' => $course['id'])));
        }
        
        return $this->render('TopxiaWebBundle:CourseExerciseManage:exercise.html.twig', array(
			'course' => $course,
			'lesson' => $lesson,
			'exercise' => $exercise
		));
	}

	public function deleteExerciseAction(Request $request, $courseId, $lessonId, $id)
	{
		list($course, $lesson) = $this->getExerciseCourseAndLesson($courseId, $lessonId);

        $exercise = $this->getExerciseService()->getExercise($id);
        if (empty($exercise)) {
        	throw $this->createNotFoundException("练习(#{$id})不存在！");
        }
        $this->getExerciseService()->deleteExercise($exercise['id']);

        return $this->createJsonResponse(true);
	}

    public function buildCheckAction(Request $request, $courseId, $lessonId)
    {
        list($course, $lesson) = $this->getExerciseCourseAndLesson($courseId, $lessonId);
        $fields = $this->generateExerciseFields($request->request->all(), $course, $lesson);
        $result = $this->getExerciseService()->canBuildExercise($fields);

        return $this->createJsonResponse($result);
    }

	private function generateExerciseFields($fields, $course, $lesson)
	{
		$fields['ranges'] = array();
    	$fields['choice'] = empty($fields['choice']) ? array() : $fields['ranges'][] = $fields['choice'];
    	$fields['single_choice'] = empty($fields['single_choice']) ? array() : $fields['ranges'][] = $fields['single_choice'];
    	$fields['uncertain_choice'] = empty($fields['uncertain_choice']) ? array() : $fields['ranges'][] = $fields['uncertain_choice'];
    	$fields['fill'] = empty($fields['fill']) ? array() : $fields['ranges'][] = $fields['fill'];
    	$fields['determine'] = empty($fields['determine']) ? array() : $fields['ranges'][] = $fields['determine'];
    	$fields['essay'] = empty($fields['essay']) ? array() : $fields['ranges'][] = $fields['essay'];
    	$fields['material'] = empty($fields['material']) ? array() : $fields['ranges'][] = $fields['material'];
    	$fields['courseId'] = $course['id'];
    	$fields['lessonId'] = $lesson['id'];

    	return $fields;
	}

    private function getExerciseCourseAndLesson($courseId, $lessonId)
    {
        $course = $this->getCourseService()->tryManageCourse($courseId);

        $lesson = $this->getCourseService()->getCourseLesson($course['id'], $lessonId);
        if (empty($lesson)) {
            throw $this->createNotFoundException("课时(#{$lessonId})不存在！");
        }

        return array($course, $lesson);
    }

    private function getExerciseService()
    {
    	return $this->getServiceKernel()->createService('Course.ExerciseService');
    }

    private function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    private function getQuestionService()
    {
        return $this->getServiceKernel()->createService('Question.QuestionService');
    }

}