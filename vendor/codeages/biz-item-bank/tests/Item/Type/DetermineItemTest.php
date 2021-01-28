<?php

namespace Tests\Item\Type;

use Codeages\Biz\ItemBank\Item\Type\DetermineItem;
use Tests\IntegrationTestCase;

class DetermineItemTest extends IntegrationTestCase
{
    public function testIsAllowMaterials()
    {
        $this->assertFalse($this->getDetermineItemProcessor()->isAllowMaterials());
    }

    /**
     * @expectedException \Codeages\Biz\ItemBank\Item\Exception\ItemException
     */
    public function testValidate_TypeInvalid_ThrowException()
    {
        $this->getDetermineItemProcessor()->validate([
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
        $this->getDetermineItemProcessor()->validate([
            'bank_id' => 1,
            'type' => 'determine',
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
        $this->getDetermineItemProcessor()->validate([
            'bank_id' => 1,
            'type' => 'determine',
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
        $this->getDetermineItemProcessor()->validate([
            'bank_id' => 1,
            'type' => 'determine',
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
        $this->getDetermineItemProcessor()->validate([
            'bank_id' => 1,
            'type' => 'determine',
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
        $this->getDetermineItemProcessor()->validate([
            'bank_id' => 1,
            'type' => 'determine',
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
        $this->getDetermineItemProcessor()->validate([
            'bank_id' => 1,
            'type' => 'determine',
            'material' => '',
            'analysis' => '',
            'category_id' => 0,
            'difficulty' => 'normal',
            'questions' => [
                ['answer_mode' => 'true_false']
            ],
        ]);
    }

    public function testReview()
    {
        $this->initData();
        $questionResponses = [
            [
                'question_id' => 4,
                'response' => ['T'],
            ],
        ];
        $determineItemProcessor = $this->getDetermineItemProcessor();
        $itemResponsesReviewResult = $determineItemProcessor->review(4, $questionResponses);
        $this->assertEquals(4, $itemResponsesReviewResult['item_id']);
        $this->assertEquals('right', $itemResponsesReviewResult['result']);
        $questionsResponseReviewResult = $itemResponsesReviewResult['question_responses_review_result'][0];
        $this->assertEquals(4, $questionsResponseReviewResult['question_id']);
        $this->assertEquals('right', $questionsResponseReviewResult['result']);
        $this->assertEquals(['right', 'none'], $questionsResponseReviewResult['response_points_result']);

        $questionResponses[0]['response'] = [];
        $itemResponsesReviewResult = $determineItemProcessor->review(4, $questionResponses);
        $this->assertEquals('wrong', $itemResponsesReviewResult['result']);
        $questionsResponseReviewResult = $itemResponsesReviewResult['question_responses_review_result'][0];
        $this->assertEquals('wrong', $questionsResponseReviewResult['result']);
        $this->assertEquals(['none', 'none'], $questionsResponseReviewResult['response_points_result']);

        $questionResponses[0]['response'] = ['F'];
        $itemResponsesReviewResult = $determineItemProcessor->review(4, $questionResponses);
        $this->assertEquals('wrong', $itemResponsesReviewResult['result']);
        $questionsResponseReviewResult = $itemResponsesReviewResult['question_responses_review_result'][0];
        $this->assertEquals('wrong', $questionsResponseReviewResult['result']);
        $this->assertEquals(['none', 'wrong'], $questionsResponseReviewResult['response_points_result']);
    }

    protected function getDetermineItemProcessor()
    {
        return new DetermineItem($this->biz);
    }

    protected function initData()
    {
        $sql = file_get_contents(__DIR__.'/../Fixtures/item.sql');

        $this->db->exec($sql);
    }
}
