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

    private function creatQuestionType()
    {
        $biz = $this->getBiz();
        $essay = new Essay();
        $essay->setBiz($biz);

        return $essay;
    }
}
