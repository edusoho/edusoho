<?php

namespace Tests\Unit\Question;

use Biz\Question\Type\Essay;
use Biz\BaseTestCase;

class EssayTest extends BaseTestCase
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

    public function testJudge()
    {
        $typeObj = $this->creatQuestionType();
        $question = array();
        $answer = array();

        $result = $typeObj->judge($question, $answer);

        $this->assertEquals('none', $result['status']);
        $this->assertEquals(0, $result['score']);
    }

    public function testFilterExistChoices()
    {
        $typeObj = $this->creatQuestionType();

        $fields = array(
            'answer' => array('this is answer'),
        );

        $filter = $typeObj->filter($fields);

        $this->assertArrayEquals($fields['answer'], $filter['answer']);
    }

    public function testGetAnswerStructure()
    {
        $typeObj = $this->creatQuestionType();

        $data = $typeObj->getAnswerStructure(array());
        
        $this->assertArrayEquals(array(0,1,2), $data);
    }

    public function testAnalysisAnswerIndex()
    {
        $typeObj = $this->creatQuestionType();

        $question = array('id' => 1, 'score' => 3);

        $data = $typeObj->analysisAnswerIndex($question, array('score' => 1));
        $this->assertArrayHasKey(1, $data);
        $this->assertArrayEquals(array(1), $data[1]);

        $data = $typeObj->analysisAnswerIndex($question, array('score' => 3));
        $this->assertArrayHasKey(1, $data);
        $this->assertArrayEquals(array(2), $data[1]);

        $data = $typeObj->analysisAnswerIndex($question, array('score' => 0));
        $this->assertArrayHasKey(1, $data);
        $this->assertArrayEquals(array(0), $data[1]);
    }

    private function creatQuestionType()
    {
        $biz = $this->getBiz();
        $essay = new Essay();
        $essay->setBiz($biz);

        return $essay;
    }
}
