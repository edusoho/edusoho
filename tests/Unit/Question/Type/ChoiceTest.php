<?php

namespace Tests\Unit\Question;

use Biz\Question\Type\Choice;
use Biz\BaseTestCase;

class ChoiceTest extends BaseTestCase
{
    public function testCreate()
    {
        $typeObj = $this->creatQuestionType();
        $result = $typeObj->create(array());
        $this->assertNull($result);
    }

    public function testUpdate()
    {
        $typeObj = $this->creatQuestionType();
        $result = $typeObj->update(1, array());
        $this->assertNull($result);
    }

    public function testDelete()
    {
        $typeObj = $this->creatQuestionType();
        $result = $typeObj->delete(1);
        $this->assertNull($result);
    }

    public function testGet()
    {
        $typeObj = $this->creatQuestionType();
        $result = $typeObj->get(1);
        $this->assertNull($result);
    }

    public function testJudgeRight()
    {
        $typeObj = $this->creatQuestionType();
        $question = array('answer' => array('1', '2'), 'score' => '2.0', 'missScore' => '1.0');
        $answer = array('1', '2');

        $result = $typeObj->judge($question, $answer);

        $this->assertEquals('right', $result['status']);
        $this->assertEquals($question['score'], $result['score']);
    }

    public function testJudgePartRight()
    {
        $typeObj = $this->creatQuestionType();
        $question = array('answer' => array('1', '2'), 'score' => '2.0', 'missScore' => '1.0');
        $answer = array('1');

        $result = $typeObj->judge($question, $answer);

        $this->assertEquals('partRight', $result['status']);
        $this->assertEquals(50, $result['percentage']);
        $this->assertEquals($question['missScore'], $result['score']);
    }

    public function testJudgeWrong()
    {
        $typeObj = $this->creatQuestionType();
        $question = array('answer' => array('1', '2'), 'score' => '2.0', 'missScore' => '1.0');
        $answer = array('3');

        $result = $typeObj->judge($question, $answer);

        $this->assertEquals('wrong', $result['status']);
        $this->assertEquals(0, $result['score']);
    }

    public function testFilterExistChoices()
    {
        $typeObj = $this->creatQuestionType();

        $fields = array(
            'choices' => array('choice1', 'choice2', 'choice3', 'choice4'),
        );

        $filter = $typeObj->filter($fields);

        $this->assertArrayEquals($fields['choices'], $filter['metas']['choices']);
    }

    public function testGetAnswerStructure()
    {
        $typeObj = $this->creatQuestionType();

        $question = array('metas' => array('choices' => array('a', 'b', 'c', 'd')));
        $data = $typeObj->getAnswerStructure($question);
        
        $this->assertArrayEquals($question['metas']['choices'], $data);
    }

    public function testAnalysisAnswerIndex()
    {
        $typeObj = $this->creatQuestionType();

        $answer = array('answer' => array(1));
        $data = $typeObj->analysisAnswerIndex(array('id' => 1), $answer);
        
        $this->assertArrayHasKey(1, $data);
        $this->assertArrayEquals($answer['answer'], $data[1]);
    }

    private function creatQuestionType()
    {
        $biz = $this->getBiz();
        $choice = new Choice();
        $choice->setBiz($biz);

        return $choice;
    }
}
