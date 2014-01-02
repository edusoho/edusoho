<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;

class QuizQuestionTestController extends BaseController
{
	public function createAction(Request $request, $courseId)
	{
		$course = $this->getCourseService()->tryManageCourse($courseId);

		$testPaper = $request->query->all();

	    if ($request->getMethod() == 'POST') {
	    	$testPaper = $request->request->all();

        	if(empty($testPaper['testPaperId'])){
	        	$result = $this->getTestService()->createTestPaper($testPaper);
        	}else{
	        	$result = $this->getTestService()->updateTestPaper($testPaper['testPaperId'], $testPaper);
        	}

	        $testPaper['courseId'] = $courseId;
	        $testPaper['testPaperId'] = $result['id'];
			return $this->redirect($this->generateUrl('course_manage_test_item',$testPaper));
        }

        if(empty($testPaper['target'])){
			throw $this->createNotFoundException('target 参数不对');
		}

        if(!empty($testPaper['testPaperId'])){
			$paper = $this->getTestService()->getTestPaper($testPaper['testPaperId']);
			$testPaper = array_merge($testPaper, $paper);
		}

		return $this->render('TopxiaWebBundle:QuizQuestionTest:create.html.twig', array(
			'course'    => $course,
			'testPaper' => $testPaper,
		));
	}

	public function indexItemAction(Request $request, $courseId, $testPaperId)
	{
		$course = $this->getCourseService()->tryManageCourse($courseId);

		if ($request->getMethod() == 'POST') {
	    	$ids = $request->request->get('ids');
	    	$scores = $request->request->get('scores');
        	$this->getTestService()->createItems($testPaperId, $ids, $scores);
        	exit();
        }
		
		$counts = $request->query->get('itemCounts');
		$scores = $request->query->get('itemScores');

		$parentTestPaper = $request->query->all();

        $questions = ArrayToolkit::index($this->getQuestionService()->findQuestionsByCourseId($courseId), 'id');

		$testPaper = $this->getTestService()->getTestPaper($testPaperId);

		$parentTestPaper = array_merge($parentTestPaper, $testPaper);

        $newQuestions = array();
        foreach ($questions as $key => $question) {

        	if($question['parentId'] != 0) {
        		$question['score'] = $scores['material'] == 0 ? $question['score'] : $scores['material'];
        		$sonQuestions[$question['parentId']][] = $question;
        	}else{
        		$question['score'] = $scores[$question['questionType']] == 0 ? $question['score'] : $scores[$question['questionType']];
        		$newQuestions[$question['questionType']][] = $question;
        	}
        }

        $seq = explode(',', $testPaper['seq']);
        $randQuestions = array();
        foreach ($seq as  $type) {

        	for($i = 0;$i<$counts[$type];$i++){
        		$rand = array_rand($newQuestions[$type]);
        		$randQuestions[] = $newQuestions[$type][$rand];
        		unset($newQuestions[$type][$rand]);
        	}	
        }

		$lessons   = ArrayToolkit::index($this->getCourseService()->getCourseLessons($courseId),'id');

		// $items     = $this->getTestService()->findItemsByTestPaperId($testPaperId);

		// $questions = ArrayToolkit::index($this->getQuestionService()->findQuestionsByIds(ArrayToolkit::column($items, 'questionId')), 'id');
		return $this->render('TopxiaWebBundle:QuizQuestionTest:index.html.twig', array(
			'course' => $course,
			// 'items' => $items,
			'questions' => $randQuestions,
			'sonQuestions' => $sonQuestions,
			'testPaper' => $testPaper,
			'parentTestPaper' => $parentTestPaper,
			'lessons' => $lessons,
		));
	}

	public function itemListAction(Request $request, $courseId,  $testPaperId)
	{
		$replaceId = $request->query->get('testItemId');

		$type = $request->query->get('type');
        if(empty($type)){
            throw $this->createNotFoundException('type 参数不对');
        }
		$type = explode('-', $type);

		$course    = $this->getCourseService()->tryManageCourse($courseId);
		$lessons   = $this->getCourseService()->getCourseLessons($courseId);
		$testPaper = $this->getTestService()->getTestPaper($testPaperId);
		$itemIds   = ArrayToolkit::column($this->getTestService()->findItemsByTestPaperIdAndQuestionType($testPaperId, $type), 'questionId');

        $conditions['target']['course'] = array($courseId);
        if (!empty($lessons)){
            $conditions['target']['lesson'] = ArrayToolkit::column($lessons,'id');;
        }

        $conditions['parentId'] = 0;
        $conditions['notId']    = $itemIds;
        $conditions[$type['0']] = $type['1'];

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

		return $this->render('TopxiaWebBundle:QuizQuestionTest:create-list.html.twig', array(
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
		$replaceId  = $request->query->get('replaceId');

		$course    = $this->getCourseService()->tryManageCourse($courseId);

		$lessons   = ArrayToolkit::index($this->getCourseService()->getCourseLessons($courseId), 'id');

		$question = $this->getQuestionService()->getQuestion($questionId);

		$questions[$question['id']] = $question; 

		$testPaper = $this->getTestService()->getTestPaper($testPaperId);

		if (!empty($replaceId)){
			$item = $this->getTestService()->updateItem($replaceId, $questionId);
		} else {
			$item = $this->getTestService()->createItem($testPaperId, $questionId);
		}
        
		return $this->render('TopxiaWebBundle:QuizQuestionTest:tr.html.twig', array(
			'course' => $course,
			'testPaperId' => $testPaperId,
			'item' => $item,
			'questions' => $questions,
			'testPaper' => $testPaper,
			'lessons' => $lessons,
		));
	}

	public function quesitonNumberCheckAction(Request $request, $courseId)
    {

		$course = $this->getCourseService()->tryManageCourse($courseId);

        $dictQuestionType = $this->getWebExtension()->getDict('questionType');

		$dictDifficulty   = $this->getWebExtension()->getDict('difficulty');

		$isDiffculty = $request->request->get('isDiffculty');

		$itemCounts  = $request->request->get('itemCounts');

		$diff = array_diff($itemCounts, $dictQuestionType);

		if(empty($diff)){
        	throw $this->createNotFoundException('参数错误');
        }
		
		$perventage['0'] = 10;
		$perventage['1'] = 40;
		$perventage['2'] = 50;

        if(($perventage['0'] + $perventage['1'] +$perventage['2']) != 100){
        	throw $this->createNotFoundException('参数错误');
        }

        $questionNumbers = $this->getQuestionService()->findQuestionsTypeNumberByCourseId($courseId);

        $message = array();

        foreach ($itemCounts as $item) {

        	list($type, $num) = $item;

        	if ($num == 0)
        		continue;

        	if ($isDiffculty == 1 ){
        		$needNums = array();
				$needNums['simple']     = (int) ($num * $perventage['0'] /100); 
				$needNums['ordinary']   = (int) ($num * $perventage['1'] /100); 
				$needNums['difficulty'] = (int) ($num * $perventage['2'] /100); 

	        	$otherNum = $num - ($needNums['simple'] + $needNums['ordinary'] + $needNums['difficulty']);

	        	if ($otherNum != 0){
	        		$randNum = array_rand($needNums, 1);
	        		$needNums[$randNum] = $needNums[$randNum] + $otherNum;
	        	}

	        	foreach ($needNums as $difficulty => $needNum) {
	        		if(empty($questionNumbers[$type][$difficulty])) {

	        			if($needNum != 0)
	        				$message[] = "{$dictQuestionType[$type]}中{$dictDifficulty[$difficulty]}缺少{$needNum}题 <br>";
	        		}
	        		else if(($needNum - $questionNumbers[$type][$difficulty]) < 0) {

	        			$needNum = abs($needNum - $questionNumbers[$type][$difficulty]);
	        			$message[] = "{$dictQuestionType[$type]}中{$dictDifficulty[$difficulty]}缺少{$needNum}题 <br>";
	        		}
	        	}
        	} else {
        		if(empty($questionNumbers[$type])){

        			$message[] = "{$dictQuestionType[$type]}缺少{$num}题 <br>";

        		}else{

        			$typeSum = 0;
        			foreach ($questionNumbers[$type] as $questionNumber) {

	        			$typeSum = $questionNumber + $typeSum;
	        		}
	        		if( ($typeSum - $num) < 0) {

	        			$needNum = abs($typeSum - $num);
	        			$message[] = "{$dictQuestionType[$type]}缺少{$needNum}题 <br>";
	        		}
        		}
        	}

        }

        if(empty($message)){
        	$message = false;
        }

        return $this->createJsonResponse($message);
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

   	private function getWebExtension()
    {
        return $this->container->get('topxia.twig.web_extension');
    }

}
