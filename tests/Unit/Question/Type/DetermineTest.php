<?php

namespace Tests\Unit\Question;

use Biz\Question\Type\Determine;
use Biz\BaseTestCase;

class DetermineTest extends BaseTestCase
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
        $question = array('answer' => array(1), 'score' => '2.0');
        $answer = array(1);

        $result = $typeObj->judge($question, $answer);

        $this->assertEquals('right', $result['status']);
        $this->assertEquals($question['score'], $result['score']);
    }

    public function testJudgeWrong()
    {
        $typeObj = $this->creatQuestionType();
        $question = array('answer' => array(1), 'score' => '2.0');
        $answer = array(0);

        $result = $typeObj->judge($question, $answer);

        $this->assertEquals('wrong', $result['status']);
        $this->assertEquals(0, $result['score']);
    }

    public function testGetAnswerStructure()
    {
        $typeObj = $this->creatQuestionType();
        $data = $typeObj->getAnswerStructure(array());
        
        $this->assertArrayEquals(array(0,1), $data);
    }

    public function analysisAnswerIndex()
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
        $determine = new Determine();
        $determine->setBiz($biz);

        return $determine;
    }
}
