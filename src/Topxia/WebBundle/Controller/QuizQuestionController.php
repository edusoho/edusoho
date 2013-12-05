<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;

class QuizQuestionController extends BaseController
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
			$this->get('request'),
			$this->getQuestionService()->searchQuestionCount($conditions),
			10
		);

		$questions = $this->getQuestionService()->searchQuestion(
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

		$lessons = $this->getCourseService()->findLessonsByIds(ArrayToolkit::column($lessons,'targetId'));

		$users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($questions, 'userId')); 

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

		$type = $request->query->get('type');

		if (!in_array($type, array('choice', 'fill', 'material', 'essay', 'determine'))) {
			$type = 'choice';
		}
		$targets = array();
		$targets[] = array('type' => 'course','id' => $course['id'],'name' => '课程');

		$LessonIds = ArrayToolkit::column($this->getCourseService()->getCourseLessons($courseId),'id');


		$targets = array(
			array('type' => 'course', 'id' => '1', 'name' => '课程'),
			array('type' => 'lesson', 'id' => '2', 'name' => '课时1'),
			array('type' => 'lesson', 'id' => '21', 'name' => '课时2'),
			array('type' => 'lesson', 'id' => '222', 'name' => '课时3'),
			array('type' => 'lesson', 'id' => '1112', 'name' => '课时4'),
		);
		var_dump($LessonIds);
		return $this->render('TopxiaWebBundle:QuizQuestion:create.html.twig', array(
			'course' => $course,
			'type' => $type,
			'targets' => $targets,
		));
	}


	private function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

   	private function getQuestionService()
   	{
   		return $this -> getServiceKernel() -> createService('Quiz.QuestionService');
   	}

}
