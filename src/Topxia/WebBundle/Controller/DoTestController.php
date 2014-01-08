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
		//权限！待补充
		//字符串要过滤js and so on
		$questions = $this->getTestService()->showTest($testId);

		$questions = $this->formatQuestions($questions);

		return $this->render('TopxiaWebBundle:QuizQuestionTest:do-test.html.twig', array(
			'questions' => $questions,
			'testId' => $testId
		));
	}

	public function submitTestAction (Request $request, $testId)
	{
		if ($request->getMethod() == 'POST') {
			$answers = $request->request->all();
			$answers = $answers['data'];

			$result = $this->getTestService()->submitTest($answers, $testId);

			return $this->createJsonResponse(true);
		}
	}

	public function finishTestAction (Request $request, $testId)
	{
		if ($request->getMethod() == 'POST') {
			$answers = $request->request->all();
			$answers = $answers['data'];

			$result = $this->getTestService()->submitTest($answers, $testId);

			$this->getTestService()->makeFinishTestResults($testId);

			exit();
		}
	}

	public function testResultsAction (Request $request, $testId)
	{

		$results = $this->getTestService()->testResults($testId);

		$accuracy = $this->makeAccuracy($results);
// var_dump($results);exit();

		return $this->render('TopxiaWebBundle:QuizQuestionTest:test-results-layout.html.twig', array(
			'results' => $results,
			'accuracy' => $accuracy,
			'testId' => $testId
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
			'all' => 0
		);
		$accuracy = array(
			'single_choice' => $accuracyResult,
			'choice' => $accuracyResult,
			'determine' => $accuracyResult,
			'fill' => $accuracyResult
		);

		foreach ($results as $value) {

			if ($value['questionType'] == 'fill') {
				$accuracy[$value['questionType']]['right'] += $value['result'];
				$accuracy[$value['questionType']]['all'] += count($value['answer']);
				continue;
			}

			$accuracy[$value['questionType']]['all']++;
			if ($value['result'] == 'right'){
				$accuracy[$value['questionType']]['right']++;
			}
			if ($value['result'] == 'wrong'){
				$accuracy[$value['questionType']]['wrong']++;
			}
			if ($value['result'] == 'noAnswer'){
				$accuracy[$value['questionType']]['noAnswer']++;
			}
		}

        return $accuracy;
    }

	private function formatQuestions ($questions)
	{
		$formatQuestions = array();
		foreach ($questions as $key => $value) {
			if ($value['questionType'] == 'single_choice') {
				$formatQuestions['single_choice'][$key] = $value;
			}
			if ($value['questionType'] == 'choice') {
				$formatQuestions['choice'][$key] = $value;
			}
			if ($value['questionType'] == 'determine') {
				$formatQuestions['determine'][$key] = $value;
			}
			if ($value['questionType'] == 'fill') {
				$formatQuestions['fill'][$key] = $value;
			}
			if ($value['questionType'] == 'essay') {
				$formatQuestions['essay'][$key] = $value;
			}
			if ($value['questionType'] == 'material') {
				$formatQuestions['material'][$key] = $value;
			}
		}
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