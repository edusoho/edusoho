<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;

use Topxia\Service\Quiz\Impl\QuestionSerialize;

class DoTestController extends BaseController
{
	public function indexAction (Request $request, $testId)
	{

		$userId = $this->getCurrentUser()->id;

		$testPaper = $this->getTestService()->getTestPaper($testId);

		$testResult = $this->getTestService()->startTest($testId, $userId, $testPaper);

		return $this->redirect($this->generateUrl('course_manage_show_test', array('id' => $testResult['id'])));
	}

	public function showTestAction (Request $request, $id)
	{

		$testResult = $this->getTestService()->getTestPaperResult($id);
		if (!$testResult) {
			throw $this->createNotFoundException('试卷不存在!');
		}
		//权限！
		if ($testResult['userId'] != $this->getCurrentUser()->id) {
			throw $this->createAccessDeniedException('不可以访问其他学生的试卷哦~');
		}

		$paper = $this->getTestService()->getTestPaper($testResult['testId']);

		//字符串要过滤js and so on?
		$questions = $this->getTestService()->showTest($id);

		$questions = $this->formatQuestions($questions);

		return $this->render('TopxiaWebBundle:QuizQuestionTest:do-test-layout.html.twig', array(
			'questions' => $questions,
			'limitTime' => $testResult['limitedTime'] * 60,
			'paper' => $paper,
			'paperResult' => $testResult,
			'id' => $id
		));
	}

	public function submitTestAction (Request $request, $id)
	{
		if ($request->getMethod() == 'POST') {
			$answers = $request->request->all();
			$answers = $answers['data'];

			$result = $this->getTestService()->submitTest($answers, $id);

			return $this->createJsonResponse(true);
		}
	}

	public function finishTestAction (Request $request, $id)
	{
		if ($request->getMethod() == 'POST') {
			$data = $request->request->all();
			$answers = $data['data'];
			$remainTime = $data['remainTime'];
			$userId = $this->getCurrentUser()->id;

			//提交变化的答案
			$results = $this->getTestService()->submitTest($answers, $id);

			//完成试卷，计算得分
			$testResults = $this->getTestService()->makeFinishTestResults($id);

			//试卷信息记录
			$this->getTestService()->finishTest($id, $userId, $remainTime);

			return $this->createJsonResponse(true);
		}
	}

	public function testResultsAction (Request $request, $id)
	{

		$paperResult = $this->getTestService()->getTestPaperResult($id);

		$paper = $this->getTestService()->getTestPaper($paperResult['testId']);

		$questions = $this->getTestService()->testResults($id);

		$accuracy = $this->makeAccuracy($questions);

		$questions = $this->formatQuestions($questions);

		return $this->render('TopxiaWebBundle:QuizQuestionTest:test-results-layout.html.twig', array(
			'questions' => $questions,
			'accuracy' => $accuracy,
			'paper' => $paper,
			'paperResult' => $paperResult,
			'id' => $id
		));
	}

	public function testPauseAction(Request $request)
	{
		return $this->render('TopxiaWebBundle:QuizQuestionTest:do-test-pause-modal.html.twig'); 
	}

	public function teacherCheckAction (Request $request, $id)
	{
		//身份校验?

		$paperResult = $this->getTestService()->getTestPaperResult($id);

		$paper = $this->getTestService()->getTestPaper($paperResult['testId']);


		if (!$teacherId = $this->getTestService()->canTeacherCheck($paper['id'])){
			throw createAccessDeniedException('无权批阅试卷！');
		}


		if ($request->getMethod() == 'POST') {
			$form = $request->request->all();
			$this->getTestService()->makeTeacherFinishTest($id, $teacherId, $form);
			
			return $this->createJsonResponse(true);
		}


		$questions = $this->getTestService()->testResults($id);

		$accuracy = $this->makeAccuracy($questions);

		$questions = $this->formatQuestions($questions);

		return $this->render('TopxiaWebBundle:QuizQuestionTest:test-teacher-check.html.twig', array(
			'questions' => $questions,
			'accuracy' => $accuracy,
			'paper' => $paper,
			'paperResult' => $paperResult,
			'id' => $id
		));
	}

	private function makeAccuracy ($questions)
    {
    	$results = array();
        foreach ($questions as $key => $question) {

            if ($question['questionType'] == 'material') {
                $results = array_merge($results, $question['questions']);
            } else {
            	$results[$key] = $question;
            }
        }

        $accuracyResult = array(
			'right' => 0,
			'wrong' => 0,
			'noAnswer' => 0,
			'all' => 0,
			'score' => 0,
			'totalScore' => 0
		);
		$accuracy = array(
			'single_choice' => $accuracyResult,
			'choice' => $accuracyResult,
			'determine' => $accuracyResult,
			'fill' => $accuracyResult,
			'essay' => $accuracyResult
		);

		foreach ($results as $value) {

			$accuracy[$value['questionType']]['score'] += $value['testResult']['score'];
			$accuracy[$value['questionType']]['totalScore'] += $value['itemScore'];

			$accuracy[$value['questionType']]['all']++;
			if ($value['testResult']['status'] == 'right'){
				$accuracy[$value['questionType']]['right']++;
			}
			if ($value['testResult']['status'] == 'wrong'){
				$accuracy[$value['questionType']]['wrong']++;
			}
			if ($value['testResult']['status'] == 'noAnswer'){
				$accuracy[$value['questionType']]['noAnswer']++;
			}
		}

        return $accuracy;
    }

	private function formatQuestions ($questions)
	{
		$formatQuestions = array();
		$number = 0;
		foreach ($questions as $key => $value) {

			if(in_array($value['questionType'], array('single_choice', 'choice'))) {
				$i = 65;
				foreach ($value['choices'] as $key => $v) {
					$v['choiceIndex'] = chr($i);
					$value['choices'][$key] = $v;
					$i++;
				}
			}

			if ($value['questionType'] == 'material') {
				$value['questions'] = $this->formatQuestions($value['questions']);
				$number += $value['questions']['number'];
				unset($value['questions']['number']);
			} else {
				$number++;
			}

			if ($value['targetId'] != 0) {
				$formatQuestions[$value['questionType']][$key] = $value;
			} else {
				$formatQuestions[$key] = $value;
			}
		}

		$formatQuestions['number'] = $number;

		return $formatQuestions;
	}

   	private function getQuestionService()
   	{
   		return $this->getServiceKernel()->createService('Quiz.QuestionService');
   	}

   	private function getTestService()
   	{
   		return $this->getServiceKernel()->createService('Quiz.TestService');
   	}

}