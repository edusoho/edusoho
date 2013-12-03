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

		$courses = $lessons = array();
		foreach ($questions as $key => $question) {
			if ($question['targetType'] == 'lesson'){
				$lessons[] = $question;
			}else if ($question['targetType'] == 'course'){
				$courses[] = $question;
			}
		}

		$lessons = $this -> getCourseService() -> findCoursesByIds(ArrayToolkit::column($lessons,'targetId'));

		$courses = $this -> getCourseService() -> findLessonsByIds(ArrayToolkit::column($courses,'targetId'));

		$users = $this -> getUserService() -> findUsersByIds(ArrayToolkit::column($questions, 'userId')); 

		return $this->render('TopxiaWebBundle:CourseManage:quiz.html.twig', array(
			'course' => $course,
			'questions' => $questions,
			'users' => $users,
			'lessons' => $lessons,
			'courses' => $courses,
			'paginator' => $paginator,
		));
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
