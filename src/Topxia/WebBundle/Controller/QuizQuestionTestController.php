<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;

class QuizQuestionTestController extends BaseController
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
		));
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
		$course = $this->getCourseService()->tryManageCourse($courseId);

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
		));
	}

	public function indexItemAction(Request $request, $courseId, $testPaperId)
	{
		$course    = $this->getCourseService()->tryManageCourse($courseId);
		$lessons   = ArrayToolkit::index($this->getCourseService()->getCourseLessons($courseId),'id');
		$testPaper = $this->getTestService()->getTestPaper($testPaperId);
		$items     = $this->getTestService()->getItemsByTestPaperId($testPaperId);

		foreach ($items as $key => $item) {
			if($item['parentId'] != 0){
				$material[$item['parentId']] = "材料题";
			}
		}
		$questions = ArrayToolkit::index($this->getQuestionService()->findQuestionsByIds(ArrayToolkit::column($items, 'questionId')), 'id'); 

		return $this->render('TopxiaWebBundle:QuizQuestionTest:item-list.html.twig', array(
			'course' => $course,
			'testPaperId' => $testPaperId,
			'items' => $items,
			'questions' => $questions,
			'testPaper' => $testPaper,
			'lessons' => $lessons,
			'material' => $material
		));
	}

	public function createItemAction(Request $request, $courseId,  $testPaperId)
	{
		$course = $this->getCourseService()->tryManageCourse($courseId);

		$target = $request->query->get('target');
		echo "<pre>";var_dump($testPaperId);header('Content-type:text/html;charset=utf-8');echo "</pre>"; exit();
	    if ($request->getMethod() == 'POST') {

            $testPaper = $request->request->all();
			$testPaper['target']   = $target;

	        $testPaper = $this->getTestService()->createTestPaper($testPaper);

	        $field['itemCount'] = $testPaper['itemCount'];
	        $field['itemScore'] = $testPaper['itemScore'];
	        $item = $this->getTestService()->createItemsByPaper($field, $testPaper['id'], $courseId);
	        
            return $this->redirect($this->generateUrl('course_manage_quiz_testPaper_create',$default));
        }

		return $this->render('TopxiaWebBundle:QuizQuestionTest:create.html.twig', array(
			'course' => $course,
			'generate' => '',
			'target' => $target,
			'isEdit' => false,
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

   	private function getTestService()
   	{
   		return $this -> getServiceKernel()->createService('Quiz.TestService');
   	}

}
