<?php

namespace Tests\Item\Type;

use Codeages\Biz\ItemBank\Item\Type\SingleChoiceItem;
use Tests\IntegrationTestCase;

class SingleChoiceItemTest extends IntegrationTestCase
{
    public function testIsAllowMaterials()
    {
        $this->assertFalse($this->getSingleChoiceItemProcessor()->isAllowMaterials());
    }

    /**
     * @expectedException \Codeages\Biz\ItemBank\Item\Exception\ItemException
     */
    public function testValidate_TypeInvalid_ThrowException()
    {
        $this->getSingleChoiceItemProcessor()->validate([
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
        $this->getSingleChoiceItemProcessor()->validate([
            'bank_id' => 1,
            'type' => 'single_choice',
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
        $this->getSingleChoiceItemProcessor()->validate([
            'bank_id' => 1,
            'type' => 'single_choice',
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
        $this->getSingleChoiceItemProcessor()->validate([
            'bank_id' => 1,
            'type' => 'single_choice',
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
        $this->getSingleChoiceItemProcessor()->validate([
            'bank_id' => 1,
            'type' => 'single_choice',
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
        $this->getSingleChoiceItemProcessor()->validate([
            'bank_id' => 1,
            'type' => 'single_choice',
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
        $this->getSingleChoiceItemProcessor()->validate([
            'bank_id' => 1,
            'type' => 'single_choice',
            'material' => '',
            'analysis' => '',
            'category_id' => 0,
            'difficulty' => 'normal',
            'questions' => [
                ['answer_mode' => 'single_choice']
            ],
        ]);
    }

    public function testReview()
    {
        $this->initData();
        $questionResponses = [
            [
                'question_id' => 6,
                'response' => ['A'],
            ],
        ];
        $singleChoiceItemProcessor = $this->getSingleChoiceItemProcessor();
        $itemResponsesReviewResult = $singleChoiceItemProcessor->review(6, $questionResponses);
        $this->assertEquals(6, $itemResponsesReviewResult['item_id']);
        $this->assertEquals('right', $itemResponsesReviewResult['result']);
        $questionsResponseReviewResult = $itemResponsesReviewResult['question_responses_review_result'][0];
        $this->assertEquals(6, $questionsResponseReviewResult['question_id']);
        $this->assertEquals('right', $questionsResponseReviewResult['result']);
        $this->assertEquals(['right', 'none', 'none', 'none'], $questionsResponseReviewResult['response_points_result']);

        $questionResponses[0]['response'] = [];
        $itemResponsesReviewResult = $singleChoiceItemProcessor->review(6, $questionResponses);
        $this->assertEquals('wrong', $itemResponsesReviewResult['result']);
        $questionsResponseReviewResult = $itemResponsesReviewResult['question_responses_review_result'][0];
        $this->assertEquals('wrong', $questionsResponseReviewResult['result']);
        $this->assertEquals(['none', 'none', 'none', 'none'], $questionsResponseReviewResult['response_points_result']);

        $questionResponses[0]['response'] = ['B'];
        $itemResponsesReviewResult = $singleChoiceItemProcessor->review(6, $questionResponses);
        $this->assertEquals('wrong', $itemResponsesReviewResult['result']);
        $questionsResponseReviewResult = $itemResponsesReviewResult['question_responses_review_result'][0];
        $this->assertEquals('wrong', $questionsResponseReviewResult['result']);
        $this->assertEquals(['none', 'wrong', 'none', 'none'], $questionsResponseReviewResult['response_points_result']);
    }

    protected function getSingleChoiceItemProcessor()
    {
        return new SingleChoiceItem($this->biz);
    }

    protected function initData()
    {
        $sql = file_get_contents(__DIR__.'/../Fixtures/item.sql');

        $this->db->exec($sql);
    }
}
