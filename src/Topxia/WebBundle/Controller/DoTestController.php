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

			var_dump($answers);exit();
			$results = $this->getTestService()->submitTest($answers, $testId);
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
   		return $this -> getServiceKernel()->createService('Quiz.QuestionService');
   	}

   	private function getTestService()
   	{
   		return $this -> getServiceKernel()->createService('Quiz.TestService');
   	}

}