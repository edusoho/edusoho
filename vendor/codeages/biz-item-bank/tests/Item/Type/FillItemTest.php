<?php

namespace Tests\Item\Type;

use Codeages\Biz\ItemBank\Item\Type\FillItem;
use Tests\IntegrationTestCase;

class FillItemTest extends IntegrationTestCase
{
    public function testIsAllowMaterials()
    {
        $this->assertFalse($this->getFillItemProcessor()->isAllowMaterials());
    }

    /**
     * @expectedException \Codeages\Biz\ItemBank\Item\Exception\ItemException
     */
    public function testValidate_TypeInvalid_ThrowException()
    {
        $this->getFillItemProcessor()->validate([
            'bank_id' => 1,
            'type' => 'essay',
            'material' => '',
            'analysis' => '',
            'category_id' => 0,
            'difficulty' => 'normal',
            'questions' => [[]],
        ]);
    }

    /**
     * @expectedException \Codeages\Biz\ItemBank\Item\Exception\ItemException
     */
    public function testValidate_QuestionNumLessThanMin_ThrowException()
    {
        $this->getFillItemProcessor()->validate([
            'bank_id' => 1,
            'type' => 'fill',
            'material' => '',
            'analysis' => '',
            'category_id' => 0,
            'difficulty' => 'normal',
            'questions' => [],
        ]);
    }

    /**
     * @expectedException \Codeages\Biz\ItemBank\Item\Exception\ItemException
     */
    public function testValidate_QuestionNumMoreThanMax_ThrowException()
    {
        $this->getFillItemProcessor()->validate([
            'bank_id' => 1,
            'type' => 'fill',
            'material' => '',
            'analysis' => '',
            'category_id' => 0,
            'difficulty' => 'normal',
            'questions' => [[], []],
        ]);
    }

    /**
     * @expectedException \Codeages\Biz\ItemBank\Item\Exception\ItemException
     */
    public function testValidate_MaterialNotEmpty_ThrowException()
    {
        $this->getFillItemProcessor()->validate([
            'bank_id' => 1,
            'type' => 'fill',
            'material' => '有材料',
            'analysis' => '',
            'category_id' => 0,
            'difficulty' => 'normal',
            'questions' => [[]],
        ]);
    }

    /**
     * @expectedException \Codeages\Biz\ItemBank\Item\Exception\ItemException
     */
    public function testValidate_AnswerModeEmpty_ThrowException()
    {
        $this->getFillItemProcessor()->validate([
            'bank_id' => 1,
            'type' => 'fill',
            'material' => '',
            'analysis' => '',
            'category_id' => 0,
            'difficulty' => 'normal',
            'questions' => [[]],
        ]);
    }

    /**
     * @expectedException \Codeages\Biz\ItemBank\Item\Exception\ItemException
     */
    public function testValidate_AnswerModeNotAllow_ThrowException()
    {
        $this->getFillItemProcessor()->validate([
            'bank_id' => 1,
            'type' => 'fill',
            'material' => '',
            'analysis' => '',
            'category_id' => 0,
            'difficulty' => 'normal',
            'questions' => [
                ['answer_mode' => 'rich_text']
            ],
        ]);
    }

    /**
     * @expectedException \Codeages\Biz\ItemBank\ItemBank\Exception\ItemBankException
     */
    public function testValidate_BankNotExist_ThrowException()
    {
        $this->getFillItemProcessor()->validate([
            'bank_id' => 1,
            'type' => 'fill',
            'material' => '',
            'analysis' => '',
            'category_id' => 0,
            'difficulty' => 'normal',
            'questions' => [
                ['answer_mode' => 'text']
            ],
        ]);
    }

    public function testReview()
    {
        $this->initData();
        $questionResponses = [
            [
                'question_id' => 3,
                'response' => ['这是答案'],
            ],
        ];
        $fillItemProcessor = $this->getFillItemProcessor();
        $itemResponsesReviewResult = $fillItemProcessor->review(3, $questionResponses);
        $this->assertEquals(3, $itemResponsesReviewResult['item_id']);
        $this->assertEquals('wrong', $itemResponsesReviewResult['result']);
        $questionsResponseReviewResult = $itemResponsesReviewResult['question_responses_review_result'][0];
        $this->assertEquals(3, $questionsResponseReviewResult['question_id']);
        $this->assertEquals('wrong', $questionsResponseReviewResult['result']);
        $this->assertEquals(['wrong', 'none'], $questionsResponseReviewResult['response_points_result']);

        $questionResponses[0]['response'] = [];
        $itemResponsesReviewResult = $fillItemProcessor->review(3, $questionResponses);
        $this->assertEquals('wrong', $itemResponsesReviewResult['result']);
        $questionsResponseReviewResult = $itemResponsesReviewResult['question_responses_review_result'][0];
        $this->assertEquals('wrong', $questionsResponseReviewResult['result']);
        $this->assertEquals(['none', 'none'], $questionsResponseReviewResult['response_points_result']);

        $questionResponses[0]['response'] = ['李白'];
        $itemResponsesReviewResult = $fillItemProcessor->review(3, $questionResponses);
        $this->assertEquals('wrong', $itemResponsesReviewResult['result']);
        $questionsResponseReviewResult = $itemResponsesReviewResult['question_responses_review_result'][0];
        $this->assertEquals('wrong', $questionsResponseReviewResult['result']);
        $this->assertEquals(['right', 'none'], $questionsResponseReviewResult['response_points_result']);

        $questionResponses[0]['response'] = ['李白', '易安居士'];
        $itemResponsesReviewResult = $fillItemProcessor->review(3, $questionResponses);
        $this->assertEquals('wrong', $itemResponsesReviewResult['result']);
        $questionsResponseReviewResult = $itemResponsesReviewResult['question_responses_review_result'][0];
        $this->assertEquals('wrong', $questionsResponseReviewResult['result']);
        $this->assertEquals(['right', 'wrong'], $questionsResponseReviewResult['response_points_result']);

        $questionResponses[0]['response'] = ['李白', '青莲居士'];
        $itemResponsesReviewResult = $fillItemProcessor->review(3, $questionResponses);
        $this->assertEquals('right', $itemResponsesReviewResult['result']);
        $questionsResponseReviewResult = $itemResponsesReviewResult['question_responses_review_result'][0];
        $this->assertEquals('right', $questionsResponseReviewResult['result']);
        $this->assertEquals(['right', 'right'], $questionsResponseReviewResult['response_points_result']);
    }

    protected function getFillItemProcessor()
    {
        return new FillItem($this->biz);
    }

    protected function initData()
    {
        $sql = file_get_contents(__DIR__.'/../Fixtures/item.sql');

        $this->db->exec($sql);
    }
}
