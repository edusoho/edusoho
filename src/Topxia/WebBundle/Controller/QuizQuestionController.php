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
		$questionType = $this->getQuestionType();

		return $this->render('TopxiaWebBundle:CourseManage:question.html.twig', array(
			'course' => $course,
			'questions' => $questions,
			'users' => $users,
			'lessons' => $lessons,
			'paginator' => $paginator,
			'parentId' => $parentId,
			'questionType' => $questionType,
		));
	}

	public function createAction(Request $request, $courseId, $type)
	{
		$course = $this->getCourseService()->tryManageCourse($courseId);
		if (!in_array($type, array('choice','single_choice', 'fill', 'material', 'essay', 'determine'))) {
			$type = 'choice';
		}
		$parentId = $request->query->get('parentId');
		$targets = $this->getQuestionTargets($courseId);
		$category = $this->getQuestionService()->findCategorysByCourseIds(array($courseId));

	    if ($request->getMethod() == 'POST') {
            $question = $request->request->all();
            if(!empty($parentId)){
            	$question['parentId'] = $parentId;
            }
	        $question = $this->getQuestionService()->createQuestion($question);

	        $this->setFlashMessage('success', '题目添加成功！');

			$submission = $request->request->get('submission');
	        if ($submission == 'continue'){

	        	$default = array(
	        		'courseId' => $courseId,
	        		'targetsDefault' => $question['targetType'].'-'.$question['targetId'],
	        		'questionDifficulty' => $question['difficulty'],
	        		'type' => $type,
	        	);
	            return $this->redirect($this->generateUrl('course_manage_quiz_question_create',$default));
	        } else if ($submission == 'submit'){

		        if ($type == 'material'){
					$parentId = $question['id'];
				}
	        	return $this->redirect($this->generateUrl('course_manage_quiz_question',array('courseId' => $courseId,'parentId' => $parentId)));
	        }
        }

		$targets['default'] = $request->query->get('targetsDefault');
		$question['difficulty'] = $request->query->get('questionDifficulty');
		$questionType = $this->getQuestionType();
        

		return $this->render('TopxiaWebBundle:QuizQuestion:create.html.twig', array(
			'course' => $course,
			'type' => $type,
			'targets' => $targets,
			'parentId' => $parentId,
			'question' => $question,
			'category' => $category,
			'questionType' => $questionType,
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
        $questionType = $this->getQuestionType();
        
        return $this->render('TopxiaWebBundle:QuizQuestion:create.html.twig', array(
			'question' => $question,
			'targets' => $targets,
			'course' => $course,
			'choice' => $choice,
			'type' => $question['questionType'],
			'isEdit' => '1',
			'category' => $category,
			'questionType' => $questionType,
		));
	}

	public function categoryAction(Request $request, $courseId)
	{
		$course = $this->getCourseService()->tryManageCourse($courseId);
		$category =	$this->getQuestionService()->findCategorysByCourseIds(array($courseId));
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
        return $this->render('TopxiaWebBundle:QuizQuestion:category-modal.html.twig', array(
            'course' => $course,
        ));
    }

    public function updateCategoryAction(Request $request, $courseId, $categoryId)
	{
		$course = $this->getCourseService()->tryManageCourse($courseId);
		$category = $this->getQuestionService()->getCategory($categoryId);
		if ($request->getMethod() == 'POST') {
			$field = $request->request->all();

            $category = $this->getQuestionService()->updateCategory($categoryId, $field);
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

    public function sortCategoriesAction(Request $request, $courseId)
	{
		$course = $this->getCourseService()->tryManageCourse($courseId);

		$this->getQuestionService()->sortCategories($course['id'], $request->request->get('ids'));
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

    public function deletesAction(Request $request, $courseId)
    {   
		$course = $this->getCourseService()->tryManageCourse($courseId);
        $ids = $request->request->get('ids');
        if(empty($ids)){
        	throw $this->createNotFoundException();
        }
        foreach ($ids as $id) {
        	$this->getQuestionService()->deleteQuestion($id);
        }
        return $this->createJsonResponse(true);
    }

    private function getQuestionTargets($courseId)
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

    private function getQuestionType()
    {
        $questionType[] = array(
	    	'choice' => '单选题',
	    	'single_choice' => '多选题',
	    	'fill' => '填空题',
	    	'determine' => '判断题',
	    	'material' => '材料题',
	    	'essay' => '问答题',
        );
        $questionType[] = array(
	    	'choice' => '选择题',
	    	'single_choice' => '选择题',
	    	'fill' => '填空题',
	    	'determine' => '判断题',
	    	'material' => '材料题',
	    	'essay' => '问答题',
        );
        return $questionType;
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
