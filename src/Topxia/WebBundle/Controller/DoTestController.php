<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;

class DoTestController extends BaseController
{
	public function indexAction (Request $request, $testId)
	{
		//权限！待补充
		$items = $this->getTestService()->findItemsByTestPaperId($testId);

		$questionIds = ArrayToolkit::column($items, 'questionId');
		$questions = $this->getQuestionService()->findQuestionsByIds($questionIds);
		$questions = ArrayToolkit::index($questions, 'id');
		$answers = $this->getQuestionService()->findChoicesByQuestionIds($questionIds);
		$answers = $this->formatAnswers($answers, $questionIds);
		$questions = $this->formatQuestions($questions);

		
		return $this->render('TopxiaWebBundle:QuizQuestionTest:do-test.html.twig', array(
			'questions' => $questions,
			'answers' => $answers,
			'testId' => $testId
		));
	}

	public function submitTestAction (Request $request, $testId)
	{
		if ($request->getMethod() == 'POST') {
			$answers = $request->request->all();
			$answers = $answers['data'];

			$results = $this->getTestService()->submitTest($answers, $testId);
			var_dump($results);exit();
		}
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
			if ($value['questionType'] == 'fill') {
				$formatQuestions['fill'][$key] = $value;
			}
			if ($value['questionType'] == 'material') {
				$formatQuestions['material'][$key] = $value;
			}
		}
		return $formatQuestions;
	}

	private function formatAnswers ($answers, $questionIds)
	{
		$formatAnswers = array();
		foreach ($answers as $value) {
			$formatAnswers[$value['questionId']][] = $value;
		}
		return $formatAnswers;
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