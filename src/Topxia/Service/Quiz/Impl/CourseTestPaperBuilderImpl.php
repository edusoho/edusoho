<?php
namespace Topxia\Service\Quiz\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\Quiz\QuestionService;

class CourseTestPaperBuilderImpl extends BaseService  implements TestPaperBuilder
{
	private $options;
	private $testPaper;
	private $questions = array();

	public function prepareBuild($testPaper,$options)
	{
		$this->testPaper = $testPaper;
		$this->options = $options;
	}

	public function build()
	{
		$this->buildSingleChoiceQuestions();
		$this->buildMultiChoiceQuestions();
		$this->buildMultiChoiceQuestions();
		$this->buildMultiChoiceQuestions();
		$this->buildMultiChoiceQuestions();
		$this->buildMultiChoiceQuestions();
	}


	public function buildSingleChoiceQuestions()
	{
		$questions = $this->getCourseAndLessonSigleChoiceOptions($this->options['courseId']);
		if($options['随机']){
     		$this->questions = array_merge($this->questions ,$this->generateRandomQuestions($questions,$options['Signle_count']));
  
		}
     	else if($options['按难度']){

     	}

	}

    private function generateQuestionByDifficuty($questions，$total)
    {

    }

	private function generateRandomQuestions($questions,$total)
	{
		if(count($questions) <= $total) return $questions;
        return array_slice(shuffle($questions),0,5);
	}

	private function getCourseAndLessonSigleChoiceOptions($courseId)
	{

	}

	private function getSigleChoiceOptions()
	{
		//return $this->options[]
	}

	public function validate()
	{

	}
	public function getQuestions()
	{

	}
	public function getValidations()
	{
		return $this->questions;
	}

    private function getQuestionService()
    {
        return $this->createService('Quiz.QuestionService');
    }

    private function getCourseService()
    {
        return $this->createService('........');
    }  

}