<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;

class QuizController extends BaseController
{
	public function indexAction(Request $request, $courseId)
	{
		$course = $this->getCourseService()->tryManageCourse($courseId);

		$LessonIds = ArrayToolkit::column($this->getCourseService()->getCourseLessons($courseId),'id');

		$conditions['target']['course'] = $courseId;
		if(isset($LessonIds)){
			$conditions['target']['lesson'] = $LessonIds;
		}

		$paginator = new Paginator(
			$this -> get('request'),
			$this -> getQuizService() -> searchQuestionCount($conditions),
			10
		);

		$questions = $this -> getQuizService() -> searchQuestions(
			$conditions,
			array('createdTime' ,'DESC'),
			$paginator->getOffsetCount(),
            $paginator->getPerPageCount()
		);

		$lessons = array();
		foreach ($questions as $question) {
			if ($question['targetType'] == 'lesson'){
				$lessons[] = $question;
			}
		}

		$lessons = $this -> getCourseService() -> findLessonsByIds(ArrayToolkit::column($lessons,'targetId'));

		$users = $this -> getUserService() -> findUsersByIds(ArrayToolkit::column($questions, 'userId')); 

		return $this->render('TopxiaWebBundle:CourseManage:question.html.twig', array(
			'course' => $course,
			'questions' => $questions,
			'users' => $users,
			'lessons' => $lessons,
			'paginator' => $paginator,
		));
	}

	public function createAction(Request $request, $courseId)
	{
		$course = $this->getCourseService()->tryManageCourse($courseId);


		return $this->render('TopxiaWebBundle:CourseManage:choice-modal.html.twig', array(
			'course' => $course,
		));
	}




	private function createQuestionChoiceForm(){
		$builder = $this -> createNameFormBuilder('');

	}


	private function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

   	private function getQuizService()
   	{
   		return $this -> getServiceKernel() -> createService('Quiz.QuizService');
   	}

}
