<?php

namespace Tests\Item\Type;

use Codeages\Biz\ItemBank\Item\Type\UncertainChoiceItem;
use Tests\IntegrationTestCase;

class UncertainChoiceItemTest extends IntegrationTestCase
{
    public function testIsAllowMaterials()
    {
        $this->assertFalse($this->getUncertainChoiceItemProcessor()->isAllowMaterials());
    }

    /**
     * @expectedException \Codeages\Biz\ItemBank\Item\Exception\ItemException
     */
    public function testValidate_TypeInvalid_ThrowException()
    {
        $this->getUncertainChoiceItemProcessor()->validate([
            'bank_id' => 1,
            'type' => 'fill',
            'material' => '',
            'analysis' => '',
            'category_id' => 0,
            'difficulty' => 'normal',
            'questions' => [
                ['answer_mode' => 'uncertain_choice']
            ],
        ]);
    }

    /**
     * @expectedException \Codeages\Biz\ItemBank\Item\Exception\ItemException
     */
    public function testValidate_QuestionNumLessThanMin_ThrowException()
    {
        $this->getUncertainChoiceItemProcessor()->validate([
            'bank_id' => 1,
            'type' => 'uncertain_choice',
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
        $this->getUncertainChoiceItemProcessor()->validate([
            'bank_id' => 1,
            'type' => 'uncertain_choice',
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
        $this->getUncertainChoiceItemProcessor()->validate([
            'bank_id' => 1,
            'type' => 'uncertain_choice',
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
        $this->getUncertainChoiceItemProcessor()->validate([
            'bank_id' => 1,
            'type' => 'uncertain_choice',
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
        $this->getUncertainChoiceItemProcessor()->validate([
            'bank_id' => 1,
            'type' => 'uncertain_choice',
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
        $this->getUncertainChoiceItemProcessor()->validate([
            'bank_id' => 1,
            'type' => 'uncertain_choice',
            'material' => '',
            'analysis' => '',
            'category_id' => 0,
            'difficulty' => 'normal',
            'questions' => [
                ['answer_mode' => 'uncertain_choice']
            ],
        ]);
    }

    public function testReview()
    {
        $this->initData();
        $questionResponses = [
            [
                'question_id' => 7,
                'response' => ['A'],
            ],
        ];
        $uncertainChoiceItemProcessor = $this->getUncertainChoiceItemProcessor();
        $itemResponsesReviewResult = $uncertainChoiceItemProcessor->review(7, $questionResponses);
        $this->assertEquals(7, $itemResponsesReviewResult['item_id']);
        $this->assertEquals('wrong', $itemResponsesReviewResult['result']);
        $questionsResponseReviewResult = $itemResponsesReviewResult['question_responses_review_result'][0];
        $this->assertEquals(7, $questionsResponseReviewResult['question_id']);
        $this->assertEquals('wrong', $questionsResponseReviewResult['result']);
        $this->assertEquals(['wrong', 'none', 'none', 'none'], $questionsResponseReviewResult['response_points_result']);

        $questionResponses[0]['response'] = [];
        $itemResponsesReviewResult = $uncertainChoiceItemProcessor->review(7, $questionResponses);
        $this->assertEquals('wrong', $itemResponsesReviewResult['result']);
        $questionsResponseReviewResult = $itemResponsesReviewResult['question_responses_review_result'][0];
        $this->assertEquals('wrong', $questionsResponseReviewResult['result']);
        $this->assertEquals(['none', 'none', 'none', 'none'], $questionsResponseReviewResult['response_points_result']);

        $questionResponses[0]['response'] = ['A', 'B'];
        $itemResponsesReviewResult = $uncertainChoiceItemProcessor->review(7, $questionResponses);
        $this->assertEquals('wrong', $itemResponsesReviewResult['result']);
        $questionsResponseReviewResult = $itemResponsesReviewResult['question_responses_review_result'][0];
        $this->assertEquals('wrong', $questionsResponseReviewResult['result']);
        $this->assertEquals(['wrong', 'right', 'none', 'none'], $questionsResponseReviewResult['response_points_result']);

        $questionResponses[0]['response'] = ['B'];
        $itemResponsesReviewResult = $uncertainChoiceItemProcessor->review(7, $questionResponses);
        $this->assertEquals('wrong', $itemResponsesReviewResult['result']);
        $questionsResponseReviewResult = $itemResponsesReviewResult['question_responses_review_result'][0];
        $this->assertEquals('wrong', $questionsResponseReviewResult['result']);
        $this->assertEquals(['none', 'right', 'none', 'none'], $questionsResponseReviewResult['response_points_result']);

        $questionResponses[0]['response'] = ['B', 'D'];
        $itemResponsesReviewResult = $uncertainChoiceItemProcessor->review(7, $questionResponses);
        $this->assertEquals('right', $itemResponsesReviewResult['result']);
        $questionsResponseReviewResult = $itemResponsesReviewResult['question_responses_review_result'][0];
        $this->assertEquals('right', $questionsResponseReviewResult['result']);
        $this->assertEquals(['none', 'right', 'none', 'right'], $questionsResponseReviewResult['response_points_result']);
    }

    protected function getUncertainChoiceItemProcessor()
    {
        return new UncertainChoiceItem($this->biz);
    }

    protected function initData()
    {
        $sql = file_get_contents(__DIR__.'/../Fixtures/item.sql');

        $this->db->exec($sql);
    }
}
