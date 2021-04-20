<?php

namespace Tests\Item\Type;

use Codeages\Biz\ItemBank\Item\Dao\QuestionDao;
use Codeages\Biz\ItemBank\Item\Type\Question;
use Tests\IntegrationTestCase;

class QuestionTest extends IntegrationTestCase
{
    /**
     * @expectedException \Codeages\Biz\ItemBank\Util\Validator\ValidatorException
     */
    public function testValidate_StemInvalid_ThrowException()
    {
        $this->getQuestionProcessor()->validate([
            'stem' => '',
            'seq' => '1',
            'score' => '2',
            'analysis' => '',
            'answer' => ['李白'],
            'response_points' => [['text' => []]],
            'answer_mode' => 'text',
        ]);
    }

    /**
     * @expectedException \Codeages\Biz\ItemBank\Util\Validator\ValidatorException
     */
    public function testValidate_SeqInvalid_ThrowException()
    {
        $this->getQuestionProcessor()->validate([
            'stem' => '诗仙[[]]',
            'seq' => 'a',
            'score' => '2',
            'analysis' => '',
            'answer' => ['李白'],
            'response_points' => [['text' => []]],
            'answer_mode' => 'text',
        ]);
    }

    /**
     * @expectedException \Codeages\Biz\ItemBank\Util\Validator\ValidatorException
     */
    public function testValidate_ScoreInvalid_ThrowException()
    {
        $this->getQuestionProcessor()->validate([
            'stem' => '诗仙[[]]',
            'seq' => '1',
            'score' => '2.15',
            'analysis' => '',
            'answer' => ['李白'],
            'response_points' => [['text' => []]],
            'answer_mode' => 'text',
        ]);
    }

    /**
     * @expectedException \Codeages\Biz\ItemBank\Util\Validator\ValidatorException
     */
    public function testValidate_NoAnalysis_ThrowException()
    {
        $this->getQuestionProcessor()->validate([
            'stem' => '诗仙[[]]',
            'seq' => '1',
            'score' => '2',
            'answer' => ['李白'],
            'response_points' => [['text' => []]],
            'answer_mode' => 'text',
        ]);
    }

    /**
     * @expectedException \Codeages\Biz\ItemBank\Util\Validator\ValidatorException
     */
    public function testValidate_AnswerInvalid_ThrowException()
    {
        $this->getQuestionProcessor()->validate([
            'stem' => '诗仙[[]]',
            'seq' => '1',
            'score' => '2',
            'analysis' => '',
            'answer' => '李白',
            'response_points' => [['text' => []]],
            'answer_mode' => 'text',
        ]);
    }

    /**
     * @expectedException \Codeages\Biz\ItemBank\Util\Validator\ValidatorException
     */
    public function testValidate_ResponsePointsInvalid_ThrowException()
    {
        $this->getQuestionProcessor()->validate([
            'stem' => '诗仙[[]]',
            'seq' => '1',
            'score' => '2',
            'analysis' => '',
            'answer' => ['李白'],
            'response_points' => 'text',
            'answer_mode' => 'text',
        ]);
    }

    /**
     * @expectedException \Codeages\Biz\ItemBank\Util\Validator\ValidatorException
     */
    public function testValidate_NoAnswerMode_ThrowException()
    {
        $this->getQuestionProcessor()->validate([
            'stem' => '诗仙[[]]',
            'seq' => '1',
            'score' => '2',
            'analysis' => '',
            'answer' => ['李白'],
            'response_points' => [['text' => []]],
            'answer_mode' => '',
        ]);
    }

    public function testProcess()
    {
        $question = [
            'id' => '',
            'stem' => '诗仙[[李白]]',
            'seq' => '1',
            'score' => '2',
            'analysis' => '',
            'answer' => ['李白'],
            'response_points' => [['text' => []]],
            'answer_mode' => 'text',
            'case_sensitive' => 1
        ];
        $result = $this->getQuestionProcessor()->process($question);
        $this->assertEquals('诗仙[[]]', $result['stem']);
        $this->assertFalse(isset($result['id']));

        $question['id'] = '1';
        $result = $this->getQuestionProcessor()->process($question);
        $this->assertEquals('1', $result['id']);
    }

    public function testReview()
    {
        $question = $this->getQuestionDao()->create([
            'item_id' => 1,
            'stem' => '诗仙[[李白]]',
            'seq' => '1',
            'score' => '2',
            'analysis' => '',
            'answer' => ['李白'],
            'response_points' => [['text' => []]],
            'answer_mode' => 'text',
            'created_user_id' => 1,
            'updated_user_id' => 1,
        ]);

        $result = $this->getQuestionProcessor()->review($question['id'], ['李白']);
        $this->assertEquals($question['id'], $result['question_id']);
        $this->assertEquals(['李白'], $result['response']);
        $this->assertEquals('right', $result['result']);
        $this->assertEquals(['right'], $result['response_points_result']);
    }

    public function getQuestionProcessor()
    {
        return new Question($this->biz);
    }

    /**
     * @return QuestionDao
     */
    protected function getQuestionDao()
    {
        return $this->biz->dao('ItemBank:Item:QuestionDao');
    }
}
