<?php
namespace Topxia\Service\Quiz\Tests;

use Topxia\Service\Common\BaseTestCase;

class QuestionServiceTest extends BaseTestCase
{   

    public function testCreateQuestionOfSingleChoice()
    {
    	$question = array(
    		'type'=>'single_choice',
    		'difficulty'=>1,
    		'stem'=>'stem',
    		'categoryId'=>1,
    		'choices'=>array('a','b','c'),
    		'answers'=>'1',
    		);

    	$createdQuestionOfSingleChoice = $this->getQuestionService()->createQuestion($question);
    	$this->assertNotNull($createdQuestionOfSingleChoice);
    	$this->assertGreaterThan(0, $createdQuestionOfSingleChoice['id']);
    }

    /**
    * @group current
    */
    public function testGetQuestionOfSingleChoice()
    {
       $question = array(
    		'type'=>'single_choice',
    		'difficulty'=>1,
    		'stem'=>'this is the stem of question',
    		'categoryId'=>1,
    		'choices'=>array('choiceA','choiceB','choiceC'),
    		'answers'=>'2'
    		);

    	$createdQuestionOfSingleChoice = $this->getQuestionService()->createQuestion($question);
    	$getedQuestionOfSingleChoice = $this->getQuestionService()->getQuestion($createdQuestionOfSingleChoice['id']);
    	
    	$this->assertGreaterThan(0, $getedQuestionOfSingleChoice['id']);
    	$this->assertEquals('choice', $getedQuestionOfSingleChoice['questionType']);
    	$this->assertEquals('this is the stem of question', $getedQuestionOfSingleChoice['stem']);
    	$this->assertEquals(0, $getedQuestionOfSingleChoice['score']);
    	$this->assertEquals(0, $getedQuestionOfSingleChoice['targetId']);
    	$this->assertEquals(0, $getedQuestionOfSingleChoice['parentId']);
    	$this->assertEquals(0, $getedQuestionOfSingleChoice['finishedTimes']);
    	$this->assertEquals(0, $getedQuestionOfSingleChoice['passedTimes']);
    	$this->assertEquals(0, $getedQuestionOfSingleChoice['updatedTime']);
    	$this->assertEquals(1, $getedQuestionOfSingleChoice['categoryId']);
    	$this->assertEquals('lesson', $getedQuestionOfSingleChoice['targetType']);
    	$this->assertEquals('simple', $getedQuestionOfSingleChoice['difficulty']);
    	$this->assertEquals(array('3'), $getedQuestionOfSingleChoice['answer']);
    	$this->assertEquals(3, $getedQuestionOfSingleChoice['choice']['isAnswer']);
    	$this->assertEmpty($getedQuestionOfSingleChoice['analysis']);
    }

    public function testUpdateQuestion()
    {
       $this->assertNull(null);
    }

    private function getQuestionService()
    {
        return $this->getServiceKernel()->createService('Quiz.QuestionService');
    }

}