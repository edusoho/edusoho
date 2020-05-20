<?php

namespace Tests\Item\Type;

use Codeages\Biz\ItemBank\Item\Type\EssayItem;
use Tests\IntegrationTestCase;

class EssayItemTest extends IntegrationTestCase
{
    /**
     * @expectedException \Codeages\Biz\ItemBank\Item\Exception\ItemException
     */
    public function testValidate_TypeInvalid_ThrowException()
    {
        $this->getEssayItemProcessor()->validate([
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
        $this->getEssayItemProcessor()->validate([
            'bank_id' => 1,
            'type' => 'essay',
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
        $this->getEssayItemProcessor()->validate([
            'bank_id' => 1,
            'type' => 'essay',
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
        $this->getEssayItemProcessor()->validate([
            'bank_id' => 1,
            'type' => 'essay',
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
        $this->getEssayItemProcessor()->validate([
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
    public function testValidate_AnswerModeNotAllow_ThrowException()
    {
        $this->getEssayItemProcessor()->validate([
            'bank_id' => 1,
            'type' => 'essay',
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
        $this->getEssayItemProcessor()->validate([
            'bank_id' => 1,
            'type' => 'essay',
            'material' => '',
            'analysis' => '',
            'category_id' => 0,
            'difficulty' => 'normal',
            'questions' => [
                ['answer_mode' => 'rich_text']
            ],
        ]);
    }

    public function testIsAllowMaterials()
    {
        $this->assertFalse($this->getEssayItemProcessor()->isAllowMaterials());
    }

    public function testReview()
    {
        $this->initData();
        $questionResponses = [
            [
                'question_id' => 5,
                'response' => ['这是答案'],
            ],
        ];
        $essayItemProcessor = $this->getEssayItemProcessor();
        $itemResponsesReviewResult = $essayItemProcessor->review(5, $questionResponses);
        $this->assertEquals(5, $itemResponsesReviewResult['item_id']);
        $this->assertEquals('none', $itemResponsesReviewResult['result']);
        $questionsResponseReviewResult = $itemResponsesReviewResult['question_responses_review_result'][0];
        $this->assertEquals(5, $questionsResponseReviewResult['question_id']);
        $this->assertEquals('none', $questionsResponseReviewResult['result']);
        $this->assertEmpty($questionsResponseReviewResult['response_points_result']);
    }

    protected function getEssayItemProcessor()
    {
        return new EssayItem($this->biz);
    }

    protected function initData()
    {
        $sql = file_get_contents(__DIR__.'/../Fixtures/item.sql');

        $this->db->exec($sql);
    }
}
