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
		if (isset($LessonIds)){
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

	public function createAction(Request $request, $courseId, $type)
	{
		$course = $this->getCourseService()->tryManageCourse($courseId);
		if (!in_array($type, array('choice', 'fill', 'material', 'essay', 'determine'))) {
			$type = 'choice';
		}
		$difficulty = array('1'=>'入门', '3'=> '初级', '5'=> '高级');
		$targets = $this->getQuestionService()->getQuestionTarget($courseId);
	    if ($request->getMethod() == 'POST') {
            $question = $request->request->all();
	        $question = $this->getQuestionService()->addQuestion($courseId, $question);
			$submission = $request->request->get('submission');
	        if ($type == 'material' && $submission == 'submit'){
	            return $this->redirect($this->generateUrl('course_manage_quiz_question_material',array('courseId'=>$courseId,'questionId'=>$question['id'])));
	        } else if ($submission == 'continue'){
	        	$targets['default'] = $question['targetType'].'-'.$question['targetId'];
	            return $this->render('TopxiaWebBundle:QuizQuestion:create.html.twig',array(
	                'course' => $course,
					'type' => $type,
					'targets' => $targets,
					'difficulty' => $difficulty,
					'question' => $question,
	            ));
	        } else if ($submission == 'submit'){
	        	return $this->redirect($this->generateUrl('course_manage_quiz_question',array('courseId'=>$courseId)));
	        }
        }
		return $this->render('TopxiaWebBundle:QuizQuestion:create.html.twig', array(
			'course' => $course,
			'type' => $type,
			'targets' => $targets,
			'difficulty' => $difficulty
		));
	}

	public function editAction(Request $request, $courseId, $id)
	{
		$course = $this->getCourseService()->tryManageCourse($courseId);
		$question = $this->getQuestionService()->getQuestion($id);
		if (empty($question)){
			throw $this->createNotFoundException("该项目问题问题不存在");
		}
		$difficulty = array('1'=>'入门', '3'=> '初级', '5'=> '高级');
		$targets = $this->getQuestionService()->getQuestionTarget($courseId);
	    if ($request->getMethod() == 'POST') {
            $question = $request->request->all();
            $question['id'] = $id;
	        $question = $this->getQuestionService()->updateQuestion($courseId, $question);
	        return $this->redirect($this->generateUrl('course_manage_quiz_question',array('courseId'=>$courseId)));
        }
        $choice = array();
        if ($question['questionType'] == "fill"){
        	$a = array_fill(0,count($question['answer']['0']),"/\(____\)/");
        	$question['stem'] = preg_replace($a, $question['answer']['0'], $question['stem'], 1);
        } else if ($question['questionType'] =="choice"){
			$choice = $this->getQuestionService()->findChoicesByQuestionIds(array($id));
			$choice['isAnswer'] = implode(',',$question['answer']);
        }
        return $this->render('TopxiaWebBundle:QuizQuestion:edit.html.twig', array(
			'difficulty' => $difficulty,
			'question' => $question,
			'targets' => $targets,
			'course' => $course,
			'choice' => $choice,
			'type' => $question['questionType'],
		));
	}


	public function materialAction(Request $request, $courseId, $questionId)
	{
		$course = $this->getCourseService()->tryManageCourse($courseId);
		$question = $this->getQuestionService()->getQuestion($questionId);
		if (empty($question)){
			throw $this->createNotFoundException("该项目问题问题不存在");
		}
		$LessonIds = ArrayToolkit::column($this->getCourseService()->getCourseLessons($courseId),'id');
		$conditions['target']['course'] = $courseId;
		if (isset($LessonIds)){
			$conditions['target']['lesson'] = $LessonIds;
		}
		$conditions['parentId'] = $questionId;
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
			if ($question['targetType'] == 'lesson')
				$lessons[] = $question;
		}
		$lessons = $this->getCourseService()->findLessonsByIds(ArrayToolkit::column($lessons,'targetId'));
		$users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($questions, 'userId')); 
		return $this->render('TopxiaWebBundle:QuizQuestion:material.html.twig', array(
			'course' => $course,
			'questions' => $questions,
			'users' => $users,
			'lessons' => $lessons,
			'paginator' => $paginator,
            'questionId' => $questionId,

		));
	}

	public function createMaterialAction(Request $request, $courseId, $type, $questionId)
	{
		$course = $this->getCourseService()->tryManageCourse($courseId);
		if (!in_array($type, array('choice', 'fill', 'material', 'essay', 'determine'))) {
			$type = 'choice';
		}
		$difficulty = array('1'=>'入门', '3'=> '初级', '5'=> '高级');
		$targets = $this->getQuestionService()->getQuestionTarget($courseId);
	    if ($request->getMethod() == 'POST') {
            $question = $request->request->all();
            $question['parentId'] = $questionId;
	        $question = $this->getQuestionService()->addQuestion($courseId, $question);
			$submission = $request->request->get('submission');
	        if ($submission == 'continue'){
	        	$targets['default'] = $question['targetType'].'-'.$question['targetId'];
	            return $this->render('TopxiaWebBundle:QuizQuestion:create.html.twig',array(
	                'course' => $course,
					'type' => $type,
					'targets' => $targets,
					'difficulty' => $difficulty,
					'question' => $question,
					'questionId' => $questionId
	            ));
	        } else if ($submission == 'submit'){
	            return $this->redirect($this->generateUrl('course_manage_quiz_question_material',array('courseId'=>$courseId,'questionId'=>$questionId)));
	        }
        }
        return $this->render('TopxiaWebBundle:QuizQuestion:create.html.twig', array(
			'course' => $course,
			'type' => $type,
			'targets' => $targets,
			'difficulty' => $difficulty,
			'questionId' => $questionId
		));
	}

	public function editMaterialAction(Request $request, $courseId, $questionId)
	{
		$course = $this->getCourseService()->tryManageCourse($courseId);
		$question = $this->getQuestionService()->getQuestion($questionId);
		if (empty($question)){
			throw $this->createNotFoundException("该项目问题问题不存在");
		}
		$difficulty = array('1'=>'入门', '3'=> '初级', '5'=> '高级');
		$targets = $this->getQuestionService()->getQuestionTarget($courseId);
	    if ($request->getMethod() == 'POST') {
            $question = $request->request->all();
            $question['id'] = $questionId;
	        $question = $this->getQuestionService()->updateQuestion($courseId, $question);
	        return $this->redirect($this->generateUrl('course_manage_quiz_question_material',array('courseId'=>$courseId,'questionId'=>$question['parentId'])));
        }
        $choice = array();
        if ($question['questionType'] == "fill"){
        	$a = array_fill(0,count($question['answer']['0']),"/\(____\)/");
        	$question['stem'] = preg_replace($a, $question['answer']['0'], $question['stem'], 1);
        } else if ($question['questionType'] =="choice"){
			$choice = $this->getQuestionService()->findChoicesByQuestionIds(array($questionId));
			$choice['isAnswer'] = implode(',',$question['answer']);
        }
        return $this->render('TopxiaWebBundle:QuizQuestion:edit.html.twig', array(
			'difficulty' => $difficulty,
			'question' => $question,
			'targets' => $targets,
			'course' => $course,
			'choice' => $choice,
			'type' => $question['questionType'],
		));
	}

	public function categoryAction(Request $request, $courseId)
	{
		$course = $this->getCourseService()->tryManageCourse($courseId);
		$conditions['target']['course'] = $courseId;
		$paginator = new Paginator(
			$this->get('request'),
			$this->getQuestionService()->searchCategoryCount($conditions),
			10
		);
		$category = $this->getQuestionService()->searchCategory(
			$conditions,
			array('createdTime' ,'DESC'),
			$paginator->getOffsetCount(),
            $paginator->getPerPageCount()
		);
        return $this->render('TopxiaWebBundle:QuizQuestion:category.html.twig', array(
			'course' => $course,
			'paginator' => $paginator,
			'categorys' => $category,
        ));
    }

    public function createCategoryAction(Request $request, $courseId)
	{
		$course = $this->getCourseService()->tryManageCourse($courseId);
		// $targets = $this->getQuestionService()->getQuestionTarget($courseId);
		if ($request->getMethod() == 'POST') {
            $category = $this->getQuestionService()->createCategory($courseId, $request->request->all());
            return $this->render('TopxiaWebBundle:QuizQuestion:tr.html.twig', array(
				'category' => $category,
				'course' => $course
	        ));
        }
        $category = array('id' => 0, 'name' => '');
        return $this->render('TopxiaWebBundle:QuizQuestion:category-create.html.twig', array(
            'course' => $course,
            // 'targets' => $targets,
            'category' => $category
        ));
    }

    public function editCategoryAction(Request $request, $courseId, $categoryId)
	{
		$course = $this->getCourseService()->tryManageCourse($courseId);
		// $targets = $this->getQuestionService()->getQuestionTarget($courseId);
		$category = $this->getQuestionService()->getCategory($categoryId);
		if ($request->getMethod() == 'POST') {
			$field = $request->request->all();
			$field['id'] = $category['id'];
            $category = $this->getQuestionService()->editCategory($courseId, $field);
            return $this->render('TopxiaWebBundle:QuizQuestion:tr.html.twig', array(
				'category' => $category,
				'course' => $course
	        ));
        }
        // $targets['default'] = $category['targetType'].'-'.$category['targetId'];
        return $this->render('TopxiaWebBundle:QuizQuestion:category-create.html.twig', array(
            'course' => $course,
            // 'targets' => $targets,
            'category' => $category
        ));
    }

	public function deleteAction(Request $request, $courseId, $id)
    {
		$course = $this->getCourseService()->tryManageCourse($courseId);
		$question = $this->getQuestionService()->getQuestion($id);
        if (empty($question)) {
            throw $this->createNotFoundException('question not found');
        }
        $this->getQuestionService()->deleteQuestion($id);
        return $this->createJsonResponse(true);
    }

    public function deletesAction(Request $request)
    {  
        $ids = $request->request->get('ids');
        if(empty($ids)){
        	throw $this->createNotFoundException();
        }
        foreach ($ids as  $id) {
        	$this->getQuestionService()->deleteQuestion($id);
        }
        return $this->createJsonResponse(true);
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
