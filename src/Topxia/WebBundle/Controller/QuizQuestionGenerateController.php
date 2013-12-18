<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;

class QuizQuestionGenerateController extends BaseController
{
	public function indexAction(Request $request, $courseId)
	{
		$course = $this->getCourseService()->tryManageCourse($courseId);
		$lessons = $this->getCourseService()->getCourseLessons($courseId);
		$parentId = $request->query->get('parentId');

		if (!empty($parentId)){
			$conditions['parentId'] = $parentId;	
		}

		$conditions['target']['course'] = $courseId;
		if (!empty($lessons)){
			$conditions['target']['lesson'] = ArrayToolkit::column($lessons,'id');;
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

		$lessons = ArrayToolkit::index($lessons,'id');
		$users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($questions, 'userId')); 

		return $this->render('TopxiaWebBundle:CourseManage:question.html.twig', array(
			'course' => $course,
			'questions' => $questions,
			'users' => $users,
			'lessons' => $lessons,
			'paginator' => $paginator,
			'parentId' => $parentId,
		));
	}

	public function createAction(Request $request, $courseId)
	{
		$course = $this->getCourseService()->tryManageCourse($courseId);
		$parentId = $request->query->get('parentId');

	    if ($request->getMethod() == 'POST') {
            $testPaper = $request->request->all();
            echo "<pre>";var_dump($testPaper);header('Content-type:text/html;charset=utf-8');echo "</pre>"; exit();
	        $question = $this->getQuestionService()->createQuestion($question);

	        $this->setFlashMessage('success', '题目添加成功！');

	        if ($submission == 'continue'){
	        	$default = array(
	        		'courseId' => $courseId,
	        		'targetsDefault' => $question['targetType'].'-'.$question['targetId'],
	        		'questionDifficulty' => $question['difficulty'],
	        		'type' => $type,
	        	);
	            return $this->redirect($this->generateUrl('course_manage_quiz_question_create',$default));
	        }
        }

		return $this->render('TopxiaWebBundle:QuizQuestionGenerate:create.html.twig', array(
			'course' => $course,
			'generate' => '',
			'isEdit' => false,
		));
	}


	public function editAction(Request $request, $courseId, $id)
	{
		$course = $this->getCourseService()->tryManageCourse($courseId);
		$question = $this->getQuestionService()->getQuestion($id);
		if (empty($question)){
			throw $this->createNotFoundException('该项目问题问题不存在');
		}
		$targets = $this->getQuestionTargets($courseId);

		$category = $this->getQuestionService()->findCategorysByCourseIds(array($courseId));

	    if ($request->getMethod() == 'POST') {

            $question = $request->request->all();

	        $question = $this->getQuestionService()->updateQuestion($id, $question);

	        $this->setFlashMessage('success', '题目修改成功！');

			return $this->redirect($this->generateUrl('course_manage_quiz_question',array('courseId'=>$courseId,'parentId' => $question['parentId'])));
        }

		$choice = array();
        if ($question['questionType'] =='choice' || $question['questionType'] =='single_choice'){
        	$choice = $question['choice'];
        	unset($question['choice']);
        }

        $targets['default'] = $question['targetType'].'-'.$question['targetId'];
        $category['default'] = $question['categoryId'];
        
        return $this->render('TopxiaWebBundle:QuizQuestion:create.html.twig', array(
			'question' => $question,
			'targets' => $targets,
			'course' => $course,
			'choice' => $choice,
			'type' => $question['questionType'],
			'isEdit' => '1',
			'category' => $category,
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
