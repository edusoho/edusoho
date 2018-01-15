<?php

namespace Tests\Unit\Question;

use Biz\Question\Type\Fill;
use Biz\BaseTestCase;

class FillTest extends BaseTestCase
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
        $question = array('answer' => array(array('2', '两'), array('1', '一')), 'score' => '2.0');
        $answer = array('2', '1');

        $result = $typeObj->judge($question, $answer);

        $this->assertEquals('right', $result['status']);
        $this->assertEquals($question['score'], $result['score']);
    }

    public function testJudgePartRight()
    {
        $typeObj = $this->creatQuestionType();
        $question = array('answer' => array(array('2', '两'), array('1', '一')), 'score' => '2.0');
        $answer = array('2', '3');

        $result = $typeObj->judge($question, $answer);

        $this->assertEquals('partRight', $result['status']);
        $this->assertEquals(50, $result['percentage']);
        $this->assertEquals(1, $result['score']);
    }

    public function testJudgeWrong()
    {
        $typeObj = $this->creatQuestionType();
        $question = array('answer' => array(array('2', '两'), array('1', '一')), 'score' => '2.0');

        $answer1 = array('2');
        $result = $typeObj->judge($question, $answer1);
        $this->assertEquals('wrong', $result['status']);
        $this->assertEquals(0, $result['score']);

        $answer2 = array('3', '4');
        $result = $typeObj->judge($question, $answer2);
        $this->assertEquals('wrong', $result['status']);
        $this->assertEquals(0, $result['score']);
    }

    /**
     * @expectedException  \Codeages\Biz\Framework\Service\Exception\InvalidArgumentException
     */
    public function testFilterTitleWrong()
    {
        $typeObj = $this->creatQuestionType();

        $fields = array(
            'stem' => '123456',
        );

        $filter = $typeObj->filter($fields);
    }

    public function testFilter()
    {
        $typeObj = $this->creatQuestionType();

        $fields = array(
            'stem' => '这是填空题[[1|一]]或[[2|二]]',
            'answer' => array(array('1|一', '2|二')),
        );

        $filter = $typeObj->filter($fields);

        $this->assertEquals(2, count($filter['answer']));
    }

    public function testGetAnswerStructure()
    {
        $typeObj = $this->creatQuestionType();

        $answer = array('answer' => array(array('a', 'b')));
        $data = $typeObj->getAnswerStructure($answer);
        
        $this->assertArrayEquals($answer['answer'], $data);
    }

    public function testAnalysisAnswerIndex()
    {
        $typeObj = $this->creatQuestionType();

        $question = array('id' => 1, 'answer' => array(array('a', 'b')));

        $data = $typeObj->analysisAnswerIndex($question, array('answer' => array('a')));
        $this->assertArrayHasKey(1, $data);
        $this->assertArrayEquals(array(0), $data[1]);

        $data = $typeObj->analysisAnswerIndex($question, array('answer' => array('c')));
        $this->assertArrayHasKey(1, $data);
        $this->assertEmpty($data[1]);
    }

    private function creatQuestionType()
    {
        $biz = $this->getBiz();
        $fill = new Fill();
        $fill->setBiz($biz);

        return $fill;
    }
}
