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

		$papers = $this->getQuestionService()->searchQuestion(
			$conditions,
			array('createdTime' ,'DESC'),
			$paginator->getOffsetCount(),
            $paginator->getPerPageCount()
		);

		$lessons = ArrayToolkit::index($lessons,'id');
		$users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($papers, 'userId')); 

		return $this->render('TopxiaWebBundle:CourseManage:paper.html.twig', array(
			'course' => $course,
			'papers' => $papers,
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
			$testPaper['target']   = $target;

	        $paper = $this->getTestService()->createPaper($testPaper);

	        $field['itemCount'] = $testPaper['itemCount'];
	        $field['itemScore'] = $testPaper['itemScore'];
	        $item = $this->getTestService()->createItemsByPaper($field, $paper['id'], $courseId);
	        echo "<pre>";var_dump($item);header('Content-type:text/html;charset=utf-8');echo "</pre>"; exit();
	        $this->setFlashMessage('success', '题目添加成功！');
            return $this->redirect($this->generateUrl('course_manage_quiz_paper_create',$default));
        }

		return $this->render('TopxiaWebBundle:QuizQuestionTest:create.html.twig', array(
			'course' => $course,
			'generate' => '',
			'target' => $target,
			'isEdit' => false,
		));
	}


	public function editAction(Request $request, $courseId, $id)
	{
		$course = $this->getCourseService()->tryManageCourse($courseId);

		$paper = $this->getQuestionService()->getQuestion($id);
		if (empty($paper)){
			throw $this->createNotFoundException('该项目问题问题不存在');
		}

		$targets = $this->getQuestionTargets($courseId);
		$category = $this->getQuestionService()->findCategorysByCourseIds(array($courseId));

	    if ($request->getMethod() == 'POST') {

            $paper = $request->request->all();

	        $paper = $this->getQuestionService()->updateQuestion($id, $paper);

	        $this->setFlashMessage('success', '题目修改成功！');

			return $this->redirect($this->generateUrl('course_manage_quiz_paper',array('courseId'=>$courseId,'parentId' => $paper['parentId'])));
        }

		$choice = array();
        if ($paper['paperType'] =='choice' || $paper['paperType'] =='single_choice'){
        	$choice = $paper['choice'];
        	unset($paper['choice']);
        }

        $targets['default'] = $paper['targetType'].'-'.$paper['targetId'];
        $category['default'] = $paper['categoryId'];
        
        return $this->render('TopxiaWebBundle:QuizQuestion:create.html.twig', array(
			'paper' => $paper,
			'targets' => $targets,
			'course' => $course,
			'choice' => $choice,
			'type' => $paper['paperType'],
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

   	private function getTestService()
   	{
   		return $this -> getServiceKernel()->createService('Quiz.TestService');
   	}

}
