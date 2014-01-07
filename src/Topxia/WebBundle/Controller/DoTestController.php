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

		return $this->render('TopxiaWebBundle:QuizQuestionTest:do-test-layout.html.twig', array(
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

		$questions = $this->getTestService()->testResults($testId);

		$accuracy = $this->makeAccuracy($questions);

		$questions = $this->formatQuestions($questions);
// var_dump($questions['essay']);exit();
		return $this->render('TopxiaWebBundle:QuizQuestionTest:test-results-layout.html.twig', array(
			'questions' => $questions,
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

			if (!in_array($value['questionType'], array('single_choice', 'choice', 'determine', 'fill'))) {
				continue;
			}

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
			}

			if ($value['targetId'] != 0) {
				$formatQuestions[$value['questionType']][$key] = $value;
			} else {
				$formatQuestions[$key] = $value;
			}
		}

		return $formatQuestions;
	}

	public function testPauseAction(Request $request)
	{
		return $this->render('TopxiaWebBundle:QuizQuestionTest:do-test-pause-modal.html.twig'); 
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