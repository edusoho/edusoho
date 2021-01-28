<?php

namespace Tests\Item\Type;

use Codeages\Biz\ItemBank\Item\Type\ChoiceItem;
use Tests\IntegrationTestCase;

class ChoiceItemTest extends IntegrationTestCase
{
    public function testIsAllowMaterials()
    {
        $this->assertFalse($this->getChoiceItemProcessor()->isAllowMaterials());
    }

    /**
     * @expectedException \Codeages\Biz\ItemBank\Item\Exception\ItemException
     */
    public function testValidate_TypeInvalid_ThrowException()
    {
        $this->getChoiceItemProcessor()->validate([
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
    public function testValidate_QuestionNumLessThanMin_ThrowException()
    {
        $this->getChoiceItemProcessor()->validate([
            'bank_id' => 1,
            'type' => 'choice',
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
        $this->getChoiceItemProcessor()->validate([
            'bank_id' => 1,
            'type' => 'choice',
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
        $this->getChoiceItemProcessor()->validate([
            'bank_id' => 1,
            'type' => 'choice',
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
        $this->getChoiceItemProcessor()->validate([
            'bank_id' => 1,
            'type' => 'choice',
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
        $this->getChoiceItemProcessor()->validate([
            'bank_id' => 1,
            'type' => 'choice',
            'material' => '',
            'analysis' => '',
            'category_id' => 0,
            'difficulty' => 'normal',
            'questions' => [
                ['answer_mode' => 'text']
            ],
        ]);
    }

    /**
     * @expectedException \Codeages\Biz\ItemBank\ItemBank\Exception\ItemBankException
     */
    public function testValidate_BankNotExist_ThrowException()
    {
        $this->getChoiceItemProcessor()->validate([
            'bank_id' => 1,
            'type' => 'choice',
            'material' => '',
            'analysis' => '',
            'category_id' => 0,
            'difficulty' => 'normal',
            'questions' => [
                ['answer_mode' => 'choice']
            ],
        ]);
    }

    public function testReview()
    {
        $this->initData();
        $questionResponses = [
            [
                'question_id' => 1,
                'response' => ['A'],
            ],
        ];
        $choiceItemProcessor = $this->getChoiceItemProcessor();
        $itemResponsesReviewResult = $choiceItemProcessor->review(1, $questionResponses);
        $this->assertEquals(1, $itemResponsesReviewResult['item_id']);
        $this->assertEquals('wrong', $itemResponsesReviewResult['result']);
        $questionsResponseReviewResult = $itemResponsesReviewResult['question_responses_review_result'][0];
        $this->assertEquals(1, $questionsResponseReviewResult['question_id']);
        $this->assertEquals('wrong', $questionsResponseReviewResult['result']);
        $this->assertEquals(['wrong', 'none', 'none', 'none'], $questionsResponseReviewResult['response_points_result']);

        $questionResponses[0]['response'] = [];
        $itemResponsesReviewResult = $choiceItemProcessor->review(1, $questionResponses);
        $this->assertEquals('wrong', $itemResponsesReviewResult['result']);
        $questionsResponseReviewResult = $itemResponsesReviewResult['question_responses_review_result'][0];
        $this->assertEquals('wrong', $questionsResponseReviewResult['result']);
        $this->assertEquals(['none', 'none', 'none', 'none'], $questionsResponseReviewResult['response_points_result']);

        $questionResponses[0]['response'] = ['A', 'B'];
        $itemResponsesReviewResult = $choiceItemProcessor->review(1, $questionResponses);
        $this->assertEquals('wrong', $itemResponsesReviewResult['result']);
        $questionsResponseReviewResult = $itemResponsesReviewResult['question_responses_review_result'][0];
        $this->assertEquals('wrong', $questionsResponseReviewResult['result']);
        $this->assertEquals(['wrong', 'right', 'none', 'none'], $questionsResponseReviewResult['response_points_result']);

        $questionResponses[0]['response'] = ['B'];
        $itemResponsesReviewResult = $choiceItemProcessor->review(1, $questionResponses);
        $this->assertEquals('wrong', $itemResponsesReviewResult['result']);
        $questionsResponseReviewResult = $itemResponsesReviewResult['question_responses_review_result'][0];
        $this->assertEquals('wrong', $questionsResponseReviewResult['result']);
        $this->assertEquals(['none', 'right', 'none', 'none'], $questionsResponseReviewResult['response_points_result']);

        $questionResponses[0]['response'] = ['B', 'D'];
        $itemResponsesReviewResult = $choiceItemProcessor->review(1, $questionResponses);
        $this->assertEquals('right', $itemResponsesReviewResult['result']);
        $questionsResponseReviewResult = $itemResponsesReviewResult['question_responses_review_result'][0];
        $this->assertEquals('right', $questionsResponseReviewResult['result']);
        $this->assertEquals(['none', 'right', 'none', 'right'], $questionsResponseReviewResult['response_points_result']);
    }

    protected function getChoiceItemProcessor()
    {
        return new ChoiceItem($this->biz);
    }

    protected function initData()
    {
        $sql = file_get_contents(__DIR__.'/../Fixtures/item.sql');

        $this->db->exec($sql);
    }
}
