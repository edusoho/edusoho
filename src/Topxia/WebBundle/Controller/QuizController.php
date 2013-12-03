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

		$LessonIds = ArrayToolkit::column($this->getQuizService()->findLessonsByCourseId($courseId),'id');

		$conditions['target']['course'] = $courseId;
		if(!empty($LessonIds)){
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

		foreach ($questions as $key => $value) {
			if ($value['targetType'] == 'lesson'){
				$target['lesson'][] = $value;
			}else if ($value['targetType'] == 'course'){
				$target['course'][] = $value;
			}
		}
		$lessons = $this -> getCourseService() -> findCoursesByIds(ArrayToolkit::column($target['lesson'],'targetId'));

		$courses = $this -> getCourseService() -> findLessonsByIds(ArrayToolkit::column($target['course'],'targetId'));

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
