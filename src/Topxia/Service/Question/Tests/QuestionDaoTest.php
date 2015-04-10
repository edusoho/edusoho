<?php
namespace Topxia\Service\Question\Tests;

use Topxia\Service\Common\BaseTestCase;
use Topxia\Common\ArrayToolkit;

class QuestionDaoTest extends BaseTestCase
{
	public function testFindQuestionsbyTypes()
	{
		$this->getQuestionDao()->findQuestionsbyTypes(array(1,2,10), 1, 10);
	}

	public function testFindQuestionsByTypesAndExcludeUnvalidatedMaterial()
	{
		$this->getQuestionDao()->findQuestionsByTypesAndExcludeUnvalidatedMaterial(array(1,2,10), 1, 10);
	}

	public function testFindQuestionsByTypesAndSourceAndExcludeUnvalidatedMaterial()
	{
		$this->getQuestionDao()->findQuestionsByTypesAndSourceAndExcludeUnvalidatedMaterial(array(1,2,10), 1, 10, "lesson", 1,10);
	}

	public function testFindQuestionsCountbyTypes()
	{
		$this->getQuestionDao()->findQuestionsCountbyTypes(array(1,2,10));
	}

	public function testFindQuestionsCountbyTypesAndSource()
	{
		$this->getQuestionDao()->findQuestionsCountbyTypesAndSource(array(1,2,10),'lesson', 1, 10);
	}

	public function testFindQuestionsByParentIds()
	{
		$this->getQuestionDao()->findQuestionsByParentIds(array(1,2,10));
	}

	public function testGetQuestionCountGroupByTypes()
	{
		$this->getQuestionDao()->getQuestionCountGroupByTypes(array("courseId"=>1));
	}

	private function getQuestionDao()
    {
        return $this->getServiceKernel()->createDao('Question.QuestionDao');
    }
}