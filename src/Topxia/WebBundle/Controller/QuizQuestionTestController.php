<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;

class QuizQuestionTestController extends BaseController
{
	public function indexAction(Request $request, $courseId)
	{
		/*$course = $this->getCourseService()->tryManageCourse($courseId);
		$lessons = $this->getCourseService()->getCourseLessons($courseId);
		$parentId = $request->query->get('parentId');

		if (!empty($parentId)){
			$conditions['parentId'] = $parentId;	
		}

		$conditions['target']['course'] = array($courseId);
		if (!empty($lessons)){
			$conditions['target']['lesson'] = ArrayToolkit::column($lessons,'id');;
		}

		$paginator = new Paginator(
			$this->get('request'),
			$this->getQuestionService()->searchQuestionCount($conditions),
			10
		);

		$testPapers = $this->getQuestionService()->searchQuestion(
			$conditions,
			array('createdTime' ,'DESC'),
			$paginator->getOffsetCount(),
            $paginator->getPerPageCount()
		);

		$lessons = ArrayToolkit::index($lessons,'id');
		$users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($testPapers, 'userId')); 

		return $this->render('TopxiaWebBundle:CourseManage:testPaper.html.twig', array(
			'course' => $course,
			'testPapers' => $testPapers,
			'users' => $users,
			'lessons' => $lessons,
			'paginator' => $paginator,
			'parentId' => $parentId,
		));*/
	}

	public function createAction(Request $request, $courseId)
	{
		$course = $this->getCourseService()->tryManageCourse($courseId);

		$target = $request->query->get('target');

	    if ($request->getMethod() == 'POST') {

            $testPaper = $request->request->all();
			$testPaper['target'] = $target;
			$field['itemCount']  = $testPaper['itemCount'];
			$field['itemScore']  = $testPaper['itemScore'];

	        $testPaper = $this->getTestService()->createTestPaper($testPaper);

	        $this->getTestService()->createItemsByTestPaper($field, $testPaper['id'], $courseId);
	        
			return $this->redirect($this->generateUrl('course_manage_test_item',array('courseId'=>$courseId,'testPaperId' => $testPaper['id'])));
        }

		return $this->render('TopxiaWebBundle:QuizQuestionTest:create.html.twig', array(
			'course'   => $course,
			'generate' => '',
			'target'   => $target,
			'isEdit'   => false,
		));
	}


	public function editAction(Request $request, $courseId, $id)
	{
		/*$course = $this->getCourseService()->tryManageCourse($courseId);

		$testPaper = $this->getQuestionService()->getQuestion($id);
		if (empty($testPaper)){
			throw $this->createNotFoundException('该项目问题问题不存在');
		}

		$targets = $this->getQuestionTargets($courseId);
		$category = $this->getQuestionService()->findCategorysByCourseIds(array($courseId));

	    if ($request->getMethod() == 'POST') {
            $testPaper = $request->request->all();
	        $testPaper = $this->getQuestionService()->updateQuestion($id, $testPaper);
	        $this->setFlashMessage('success', '题目修改成功！');
			return $this->redirect($this->generateUrl('course_manage_quiz_testPaper',array('courseId'=>$courseId,'testPaperId' => $testPaper['id'])));
        }

		$choice = array();
        if ($testPaper['testPaperType'] =='choice' || $testPaper['testPaperType'] =='single_choice'){
        	$choice = $testPaper['choice'];
        	unset($testPaper['choice']);
        }

        $targets['default'] = $testPaper['targetType'].'-'.$testPaper['targetId'];
        $category['default'] = $testPaper['categoryId'];
        
        return $this->render('TopxiaWebBundle:QuizQuestion:create.html.twig', array(
			'testPaper' => $testPaper,
			'targets' => $targets,
			'course' => $course,
			'choice' => $choice,
			'type' => $testPaper['testPaperType'],
			'isEdit' => '1',
			'category' => $category,
		));*/
	}

	public function indexItemAction(Request $request, $courseId, $testPaperId)
	{
		$course    = $this->getCourseService()->tryManageCourse($courseId);
		$lessons   = ArrayToolkit::index($this->getCourseService()->getCourseLessons($courseId),'id');
		$testPaper = $this->getTestService()->getTestPaper($testPaperId);
		$items     = $this->getTestService()->findItemsByTestPaperId($testPaperId);

		$questions = ArrayToolkit::index($this->getQuestionService()->findQuestionsByIds(ArrayToolkit::column($items, 'questionId')), 'id');

		return $this->render('TopxiaWebBundle:QuizQuestionItem:index.html.twig', array(
			'course' => $course,
			'testPaperId' => $testPaperId,
			'items' => $items,
			'questions' => $questions,
			'testPaper' => $testPaper,
			'lessons' => $lessons,
		));
	}

	public function itemListAction(Request $request, $courseId,  $testPaperId)
	{
		$type = $request->query->get('type');
		$replaceId = $request->query->get('testItemId');

		$type = explode('-', $type);

        if(count($type) != 2){
            throw $this->createNotFoundException('type 参数不对');
        }

		$course    = $this->getCourseService()->tryManageCourse($courseId);
		$lessons   = $this->getCourseService()->getCourseLessons($courseId);

		$testPaper = $this->getTestService()->getTestPaper($testPaperId);
		$itemIds   = ArrayToolkit::column($this->getTestService()->findItemsByTestPaperIdAndQuestionType($testPaperId, $type), 'questionId');

        $conditions['target']['course'] = array($courseId);
        if (!empty($lessons)){
            $conditions['target']['lesson'] = ArrayToolkit::column($lessons,'id');;
        }

        $conditions['parentId'] = 0;
        $conditions[$type['0']] = $type['1'];
        $conditions['notId']    = $itemIds;

        $paginator = new Paginator(
			$this->get('request'),
			$this->getQuestionService()->searchQuestionCount($conditions),
			5
		);

        $questions = $this->getQuestionService()->searchQuestion(
        		$conditions, 
        		array('createdTime' ,'DESC'), 
        		$paginator->getOffsetCount(),
                $paginator->getPerPageCount()
        );

		$lessons = ArrayToolkit::index($lessons,'id');

		$users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($questions, 'userId')); 

		return $this->render('TopxiaWebBundle:QuizQuestionItem:create-list.html.twig', array(
			'course' => $course,
			'lessons' => $lessons,
			'questions' => $questions,
			'testPaper' => $testPaper,
			'parentId' => false,
			'users' => $users,
			'paginator' => $paginator,
			'replaceId' => $replaceId
		));
	}

	public function createItemAction(Request $request, $courseId,  $testPaperId)
	{
		$questionId = $request->query->get('questionId');
		$replaceId = $request->query->get('replaceId');

		$course    = $this->getCourseService()->tryManageCourse($courseId);

		$lessons   = ArrayToolkit::index($this->getCourseService()->getCourseLessons($courseId), 'id');

		$question = $this->getQuestionService()->getQuestion($questionId);

		$questions[$question['id']] = $question; 

		$testPaper = $this->getTestService()->getTestPaper($testPaperId);
		if(!empty($replaceId)){
			$this->getTestService()->deleteItem($replaceId);
		}

        $item = $this->getTestService()->createItem($testPaperId, $questionId);


		return $this->render('TopxiaWebBundle:QuizQuestionItem:tr.html.twig', array(
			'course' => $course,
			'testPaperId' => $testPaperId,
			'item' => $item,
			'questions' => $questions,
			'testPaper' => $testPaper,
			'lessons' => $lessons,
		));
	}

	public function deleteItemAction(Request $request, $courseId, $testItemId)
    {
		$course = $this->getCourseService()->tryManageCourse($courseId);
        $item = $this->getTestService()->getTestItem($testItemId);
        if (empty($item)) {
            throw $this->createNotFoundException();
        }
        $this->getTestService()->deleteItem($testItemId);
        return $this->createJsonResponse(true);
    }

    public function deleteItemsAction(Request $request, $courseId)
    {   
		$course = $this->getCourseService()->tryManageCourse($courseId);
        $ids = $request->request->get('ids');
        if(empty($ids)){
        	throw $this->createNotFoundException();
        }
        foreach ($ids as $id) {
        	$this->getTestService()->deleteItem($id);
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

   	private function getTestService()
   	{
   		return $this -> getServiceKernel()->createService('Quiz.TestService');
   	}

}
