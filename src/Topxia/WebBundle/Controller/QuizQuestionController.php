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

	public function createAction(Request $request, $courseId, $type)
	{
		$course = $this->getCourseService()->tryManageCourse($courseId);
		if (!in_array($type, array('choice','single_choice', 'fill', 'material', 'essay', 'determine'))) {
			$type = 'choice';
		}
		$parentId = $request->query->get('parentId');
		$targets = $this->getQuestionTarget($courseId);

	    if ($request->getMethod() == 'POST') {
            $question = $request->request->all();
            if(!empty($parentId)){
            	$question['parentId'] = $parentId;
            }
	        $question = $this->getQuestionService()->createQuestion($question);

			$submission = $request->request->get('submission');
	        if ($submission == 'continue'){
	        	$targets['default'] = $question['targetType'].'-'.$question['targetId'];
	            return $this->render('TopxiaWebBundle:QuizQuestion:create.html.twig',array(
	                'course' => $course,
					'type' => $type,
					'targets' => $targets,
					'question' => $question,
					'parentId' => $parentId,
	            ));
	        } else if ($submission == 'submit'){
		        if ($type == 'material'){
					$parentId = $question['id'];
				}
	        	return $this->redirect($this->generateUrl('course_manage_quiz_question',array("courseId" => $courseId,"parentId" => $parentId)));
	        }
        }

		return $this->render('TopxiaWebBundle:QuizQuestion:create.html.twig', array(
			'course' => $course,
			'type' => $type,
			'targets' => $targets,
			'parentId' => $parentId,
		));
	}


	public function editAction(Request $request, $courseId, $id)
	{
		$course = $this->getCourseService()->tryManageCourse($courseId);
		$question = $this->getQuestionService()->getQuestion($id);
		if (empty($question)){
			throw $this->createNotFoundException("该项目问题问题不存在");
		}
		$targets = $this->getQuestionTarget($courseId);

	    if ($request->getMethod() == 'POST') {
            $question = $request->request->all();
            $question['id'] = $id;
	        $question = $this->getQuestionService()->updateQuestion($question);
	        if($question['parentId'] == '0'){
		        return $this->redirect($this->generateUrl('course_manage_quiz_question',array('courseId'=>$courseId)));
	        }else{
		        return $this->redirect($this->generateUrl('course_manage_quiz_question',array('courseId'=>$courseId,'parentId' => $question['parentId'])));
	        }
        }
		$choice = array();
        if ($question['questionType'] =="choice" || $question['questionType'] =="single_choice"){
        	$choice = $question['choice'];
        	unset($question['choice']);
        }

        $targets['default'] = $question['targetType'].'-'.$question['targetId'];

        return $this->render('TopxiaWebBundle:QuizQuestion:create.html.twig', array(
			'question' => $question,
			'targets' => $targets,
			'course' => $course,
			'choice' => $choice,
			'type' => $question['questionType'],
			'isEdit' => '1',
		));
	}

	public function categoryAction(Request $request, $courseId)
	{
		$course = $this->getCourseService()->tryManageCourse($courseId);
		$category =	$this->getQuestionService()->findCategoryByCourseIds(array($courseId));
        return $this->render('TopxiaWebBundle:QuizQuestion:index-category.html.twig', array(
			'categorys' => $category,
			'course' => $course,
        ));
    }

    public function createCategoryAction(Request $request, $courseId)
	{
		$course = $this->getCourseService()->tryManageCourse($courseId);
		if ($request->getMethod() == 'POST') {
			$field =$request->request->all();
			$field['courseId'] = $courseId;
            $category = $this->getQuestionService()->createCategory($field);
            return $this->render('TopxiaWebBundle:QuizQuestion:tr.html.twig', array(
				'category' => $category,
				'course' => $course
	        ));
        }
        $category = array('id' => 0, 'name' => '');
        return $this->render('TopxiaWebBundle:QuizQuestion:category-modal.html.twig', array(
            'category' => $category,
            'course' => $course,
        ));
    }

    public function editCategoryAction(Request $request, $courseId, $categoryId)
	{
		$course = $this->getCourseService()->tryManageCourse($courseId);
		$category = $this->getQuestionService()->getCategory($categoryId);
		if ($request->getMethod() == 'POST') {
			$field = $request->request->all();
			$field['id'] = $category['id'];
            $category = $this->getQuestionService()->editCategory($field);
            return $this->render('TopxiaWebBundle:QuizQuestion:tr.html.twig', array(
				'category' => $category,
				'course' => $course,
	        ));
        }
        return $this->render('TopxiaWebBundle:QuizQuestion:category-modal.html.twig', array(
            'category' => $category,
            'course' => $course,
        ));
    }

    public function sortAction(Request $request, $courseId)
	{
		$course = $this->getCourseService()->tryManageCourse($courseId);
		$this->getQuestionService()->sortCategory($course['id'], $request->request->get('ids'));
		return $this->createJsonResponse(true);
	}

    public function deleteCategoryAction(Request $request, $courseId, $categoryId)
    {
		$course = $this->getCourseService()->tryManageCourse($courseId);
        $category = $this->getQuestionService()->getCategory($categoryId);
        if (empty($category)) {
            throw $this->createNotFoundException();
        }
        $this->getQuestionService()->deleteCategory($categoryId);
        return $this->createJsonResponse(true);
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


    private function getQuestionTarget($courseId)
    {
        $course = $this->getCourseService()->getCourse($courseId);
        if (empty($course))
            return null;
        $lessons = $this->getCourseService()->getCourseLessons($courseId);

        $targets = array();
        $targets[] = array('type' => 'course','id' => $course['id'],'name' => '课程');
        foreach ($lessons as  $lesson) {
            $targets[] = array('type' => 'lesson','id' => $lesson['id'],'name' => '课时'.$lesson['number']);
        }

        return $targets;
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
