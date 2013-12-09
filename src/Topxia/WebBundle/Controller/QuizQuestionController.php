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
		if(empty($type)){
			$type = $request->request->get('type');
		}

		$parentId = $request->query->get('parentId');

		if (!in_array($type, array('choice', 'fill', 'material', 'essay', 'determine'))) {
			$type = 'choice';
		}

		$difficulty = array('1'=>'入门', '3'=> '初级', '5'=> '高级');
		$targets = $this->getQuestionService()->getQuestionTarget($courseId);

	    if ($request->getMethod() == 'POST') {


            $question = $request->request->all();
	        if (empty($question['id'])) {
	            $question = $this->getQuestionService()->addQuestion($courseId, $question);
	        } else {
	            $question = $this->getQuestionService()->updateQuestion($question['id'], $question);
	        }

	        if($type == 'material'){
	            return $this->render('TopxiaWebBundle:QuizQuestion:create-material.html.twig',array(
	                'question' => $question,
	                'course' => $course,
	            ));
	        }
	        
			$submission = $request->request->get('submission');
	        if($submission == 'continue'){
	        	$targets['default'] = $question['targetType'].'-'.$question['targetId'];
	            return $this->render('TopxiaWebBundle:QuizQuestion:create.html.twig',array(
	                'course' => $course,
					'type' => $type,
					'targets' => $targets,
					'difficulty' => $difficulty,
					'question' => $question,
	            ));
	        }else if($submission == 'submit'){
	        	return $this->redirect($this->generateUrl('course_manage_quiz_question',array('courseId'=>$courseId)));
	        }
	        
			
        }

		return $this->render('TopxiaWebBundle:QuizQuestion:create.html.twig', array(
			'course' => $course,
			'type' => $type,
			'parentId' => $parentId,
			'targets' => $targets,
			'difficulty' => $difficulty
		));
	}


	private function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

   	private function getQuestionService()
   	{
   		return $this -> getServiceKernel()->createService('Quiz.QuestionService');
   	}

}
