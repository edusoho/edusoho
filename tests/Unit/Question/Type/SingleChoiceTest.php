<?php

namespace Tests\Unit\Question;

use Biz\Question\Type\SingleChoice;
use Biz\BaseTestCase;
use Biz\Common\HTMLHelper;

class SingleChoiceTest extends BaseTestCase
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
        $question = array('answer'=>array('1'),'score'=>'2.0');
        $answer = array('1');

        $result = $typeObj->judge($question, $answer);

        $this->assertEquals('right', $result['status']);
        $this->assertEquals($question['score'], $result['score']);
    }

    public function testJudgeWrong()
    {
        $typeObj = $this->creatQuestionType();
        $question = array('answer'=>array('1'),'score'=>'2.0');
        $answer = array('2');

        $result = $typeObj->judge($question, $answer);

        $this->assertEquals('wrong', $result['status']);
        $this->assertEquals(0, $result['score']);
    }

    public function testFilterExistChoices()
    {
        $typeObj = $this->creatQuestionType();

        $fields = array(
            'choices' => array('choice1','choice2','choice3','choice4')
        );

        $filter = $typeObj->filter($fields);

        $this->assertArrayEquals($fields['choices'], $filter['metas']['choices']);
    }

    private function creatQuestionType()
    {
        $biz = $this->getBiz();
        $singleChoice = new SingleChoice();
        $singleChoice->setBiz($biz);

        return $singleChoice;
    }
}
