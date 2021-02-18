<?php

namespace Tests\Item\Service;

use Codeages\Biz\ItemBank\Item\Dao\ItemDao;
use Codeages\Biz\ItemBank\Item\Dao\QuestionDao;
use Codeages\Biz\ItemBank\Item\Service\ItemService;
use Tests\IntegrationTestCase;

class ItemServiceTest extends IntegrationTestCase
{
    public function testCreateSingleChoiceItem()
    {
        $this->mockItemBankService();
        $this->mockItemCategoryService();
        $fields = $this->getSingleChoiceItemFields();
        $item = $this->getItemService()->createItem($fields);

        $this->assertEquals(1, $item['question_num']);
        $this->assertEquals($fields['type'], $item['type']);
    }

    public function testCreateUncertainChoiceItem()
    {
        $this->mockItemBankService();
        $this->mockItemCategoryService();
        $fields = $this->getUncertainChoiceItemFields();
        $item = $this->getItemService()->createItem($fields);

        $this->assertEquals(1, $item['question_num']);
        $this->assertEquals($fields['type'], $item['type']);
    }

    public function testCreateChoiceItem()
    {
        $this->mockItemBankService();
        $this->mockItemCategoryService();
        $fields = $this->getChoiceItemFields();
        $item = $this->getItemService()->createItem($fields);

        $this->assertEquals(1, $item['question_num']);
        $this->assertEquals($fields['type'], $item['type']);
    }

    public function testCreateDetermineItem()
    {
        $this->mockItemBankService();
        $this->mockItemCategoryService();
        $fields = $this->getDetermineItemFields();
        $item = $this->getItemService()->createItem($fields);

        $this->assertEquals(1, $item['question_num']);
        $this->assertEquals($fields['type'], $item['type']);
    }

    public function testCreateFillItem()
    {
        $this->mockItemBankService();
        $this->mockItemCategoryService();
        $fields = $this->getFillItemFields();
        $item = $this->getItemService()->createItem($fields);

        $this->assertEquals(1, $item['question_num']);
        $this->assertEquals($fields['type'], $item['type']);
    }

    public function testCreateEssayItem()
    {
        $this->mockItemBankService();
        $this->mockItemCategoryService();
        $fields = $this->getEssayItemFields();
        $item = $this->getItemService()->createItem($fields);

        $this->assertEquals(1, $item['question_num']);
        $this->assertEquals($fields['type'], $item['type']);
    }

    public function testCreateMaterialItem()
    {
        $this->mockItemBankService();
        $this->mockItemCategoryService();
        $fields = $this->getMaterialItemFields();
        $item = $this->getItemService()->createItem($fields);

        $this->assertEquals(5, $item['question_num']);
        $this->assertEquals($fields['type'], $item['type']);
    }

    /**
     * @expectedException \Codeages\Biz\ItemBank\Item\Exception\ItemException
     * @expectedExceptionCode \Codeages\Biz\ItemBank\ErrorCode::ITEM_ARGUMENT_INVALID
     */
    public function testCreateItemWithoutType()
    {
        $this->mockItemBankService();
        $this->mockItemCategoryService();
        $fields = $this->getSingleChoiceItemFields();
        unset($fields['type']);

        $this->getItemService()->createItem($fields);
    }

    public function testImportItems()
    {
        $this->mockItemBankService();
        $this->mockItemCategoryService();
        $items = [
            $this->getSingleChoiceItemFields(),
            $this->getChoiceItemFields(),
        ];
        $result = $this->getItemService()->importItems($items, 5);

        $this->assertEquals(5, $result[0]['bank_id']);
        $this->assertEquals('single_choice', $result[0]['type']);
    }

    public function testParseItems()
    {
        $filename = dirname(__DIR__).'/Fixtures/word.docx';
        $text = $this->getItemService()->readWordFile($filename, '');
        $result = $this->getItemService()->parseItems($text);

        $this->assertEquals('single_choice', $result[0]['type']);
        $this->assertEquals('choice', $result[1]['type']);
        $this->assertCount(2, $result);
    }

    public function testUpdateItem()
    {
        $this->mockItemBankService();
        $this->mockItemCategoryService();
        $fields = $this->getSingleChoiceItemFields();
        $item = $this->createItem($fields);

        $fields['category_id'] = '2';
        $fields['difficulty'] = 'simple';
        $fields['question_num'] = 1;
        $fields['questions'][0]['score'] = '3.0';

        $item = $this->getItemService()->updateItem($item['id'], $fields);
        $questions = $this->getQuestionDao()->findByItemId($item['id']);
        $question = array_shift($questions);

        $this->assertEquals(1, $item['question_num']);
        $this->assertEquals($fields['category_id'], $item['category_id']);
        $this->assertEquals($fields['difficulty'], $item['difficulty']);
        $this->assertEquals($fields['questions'][0]['score'], $question['score']);
    }

    public function testUpdateMaterialItem()
    {
        $this->mockItemBankService();
        $this->mockItemCategoryService();
        $item = $this->createItem($this->getMaterialItemFields());

        $questions = $this->getQuestionDao()->findByItemId($item['id']);
        array_shift($questions);
        array_shift($questions);
        $question = array_shift($questions);
        $question['stem'] = '这是多选题题干';
        $questions[] = $question;
        $questions[] = [
            'stem' => '<p>这是题干</p>',
            'seq' => '7',
            'score' => '2.0',
            'response_points' => [],
            'answer' => ['这是答案'],
            'analysis' => '<p>这是解析</p>',
            'answer_mode' => 'rich_text',
        ];
        foreach ($questions as &$question) {
            $question['attachments'] = [];
        }
        $item['questions'] = $questions;

        $item = $this->getItemService()->updateItem($item['id'], $item);

        $this->assertEquals(4, $item['question_num']);
        $this->assertEquals(4, $this->getQuestionDao()->count(['item_id' => $item['id']]));
    }

    /**
     * @expectedException \Codeages\Biz\ItemBank\Item\Exception\ItemException
     * @expectedExceptionCode \Codeages\Biz\ItemBank\ErrorCode::ITEM_NOT_FOUND
     */
    public function testUpdateItem_NotExist_ThrowException()
    {
        $this->mockItemBankService();
        $this->mockItemCategoryService();
        $this->getItemService()->updateItem(1, $this->getSingleChoiceItemFields());
    }

    public function testGetItem()
    {
        $this->mockItemBankService();
        $this->mockItemCategoryService();
        $fields = $this->getSingleChoiceItemFields();
        $item = $this->createItem($fields);
        $item = $this->getItemService()->getItem($item['id']);

        $this->assertEquals(1, $item['question_num']);
        $this->assertEquals($fields['type'], $item['type']);
    }

    public function testGetItemWithQuestions()
    {
        $this->mockItemBankService();
        $this->mockItemCategoryService();
        $fields = $this->getSingleChoiceItemFields();
        $item = $this->createItem($fields);
        $item = $this->getItemService()->getItemWithQuestions($item['id']);

        $this->assertEquals(1, $item['question_num']);
        $this->assertEquals($fields['type'], $item['type']);
        $this->assertEquals(1, count($item['questions']));
        $question = array_shift($item['questions']);
        $this->assertEquals($fields['questions'][0]['answer_mode'], $question['answer_mode']);
    }

    public function testGetItemWithQuestionWithAnswer()
    {
        $this->mockItemBankService();
        $this->mockItemCategoryService();
        $fields = $this->getSingleChoiceItemFields();
        $item = $this->createItem($fields);
        $item = $this->getItemService()->getItemWithQuestions($item['id'], true);

        $this->assertEquals($fields['analysis'], $item['analysis']);
        $question = array_shift($item['questions']);
        $this->assertEquals($fields['questions'][0]['answer'], $question['answer']);
        $this->assertEquals($fields['questions'][0]['analysis'], $question['analysis']);
    }

    public function testGetItemWithQuestionWithoutAnswer()
    {
        $this->mockItemBankService();
        $this->mockItemCategoryService();
        $item = $this->createItem($this->getSingleChoiceItemFields());
        $item = $this->getItemService()->getItemWithQuestions($item['id']);

        $this->assertFalse(isset($item['analysis']));
        $question = array_shift($item['questions']);
        $this->assertFalse(isset($question['answer']));
        $this->assertFalse(isset($question['analysis']));
    }

    public function testSearchItems()
    {
        $this->mockItemBankService();
        $this->mockItemCategoryService();
        $this->createItem($this->getSingleChoiceItemFields());
        $this->createItem($this->getUncertainChoiceItemFields());
        $this->createItem($this->getChoiceItemFields());
        $this->createItem($this->getDetermineItemFields());
        $this->createItem($this->getFillItemFields());
        $this->createItem($this->getEssayItemFields());
        $this->createItem($this->getMaterialItemFields());

        $items = $this->getItemService()->searchItems(['bank_id' => 1], ['created_time' => 'ASC'], 0, PHP_INT_MAX);
        $this->assertEquals(7, count($items));

        $items = $this->getItemService()->searchItems(['type' => 'fill'], ['created_time' => 'ASC'], 0, PHP_INT_MAX);
        $this->assertEquals(1, count($items));

        $items = $this->getItemService()->searchItems(['keyword' => '题干'], ['created_time' => 'ASC'], 0, PHP_INT_MAX);
        $this->assertEquals(5, count($items));

        $items = $this->getItemService()->searchItems(['keyword' => '材料'], ['created_time' => 'ASC'], 0, PHP_INT_MAX);
        $this->assertEquals(1, count($items));
    }

    public function testCountItem()
    {
        $this->mockItemBankService();
        $this->mockItemCategoryService();
        $this->createItem($this->getSingleChoiceItemFields());
        $this->createItem($this->getUncertainChoiceItemFields());
        $this->createItem($this->getChoiceItemFields());
        $this->createItem($this->getDetermineItemFields());
        $this->createItem($this->getFillItemFields());
        $this->createItem($this->getEssayItemFields());
        $this->createItem($this->getMaterialItemFields());

        $itemCount = $this->getItemService()->countItems(['bank_id' => 1]);

        $this->assertEquals(7, $itemCount);
    }

    public function testDeleteItem()
    {
        $this->mockItemBankService();
        $this->mockItemCategoryService();
        $item = $this->createItem($this->getSingleChoiceItemFields());
        $this->getItemService()->deleteItem($item['id']);
        $result = $this->getItemDao()->get($item['id']);

        $this->assertEmpty($result);
    }

    public function testDeleteItems()
    {
        $this->mockItemBankService();
        $this->mockItemCategoryService();
        $singleChoiceItem = $this->createItem($this->getSingleChoiceItemFields());
        $choiceItem = $this->createItem($this->getChoiceItemFields());
        $uncertainChoiceItem = $this->createItem($this->getUncertainChoiceItemFields());
        $this->assertEquals(1, $this->getQuestionDao()->count(['item_id' => $singleChoiceItem['id']]));
        $this->assertEquals(1, $this->getQuestionDao()->count(['item_id' => $choiceItem['id']]));
        $this->assertEquals(1, $this->getQuestionDao()->count(['item_id' => $uncertainChoiceItem['id']]));
        $this->getItemService()->deleteItems([$singleChoiceItem['id'], $uncertainChoiceItem['id'], $choiceItem['id']]);

        $this->assertEquals(0, $this->getQuestionDao()->count(['id' => $singleChoiceItem['id']]));
        $this->assertEquals(0, $this->getQuestionDao()->count(['id' => $choiceItem['id']]));
        $this->assertEquals(0, $this->getQuestionDao()->count(['id' => $uncertainChoiceItem['id']]));
        $this->assertEquals(0, $this->getQuestionDao()->count(['item_id' => $singleChoiceItem['id']]));
        $this->assertEquals(0, $this->getQuestionDao()->count(['item_id' => $choiceItem['id']]));
        $this->assertEquals(0, $this->getQuestionDao()->count(['item_id' => $uncertainChoiceItem['id']]));
    }

    public function testUpdateItemsCategoryId()
    {
        $this->mockItemBankService();
        $this->mockItemCategoryService();

        $singleChoiceItem = $this->createItem($this->getSingleChoiceItemFields());
        $choiceItem = $this->createItem($this->getChoiceItemFields());
        $this->assertEquals(1, $singleChoiceItem['category_id']);
        $this->assertEquals(1, $choiceItem['category_id']);

        $updateIds = [$singleChoiceItem['id'], $choiceItem['id']];
        $this->getItemService()->updateItemsCategoryId($updateIds, 2);

        $singleChoiceItem = $this->getItemDao()->get($singleChoiceItem['id']);
        $choiceItem = $this->getItemDao()->get($choiceItem['id']);
        $this->assertEquals(2, $singleChoiceItem['category_id']);
        $this->assertEquals(2, $choiceItem['category_id']);
    }

    public function testReviewSingleChoiceItem()
    {
        $singleChoiceItem = $this->createItem($this->getSingleChoiceItemFields());
        $questions = $this->getQuestionDao()->findByItemId($singleChoiceItem['id']);
        $itemResponses = [
            [
                'item_id' => $singleChoiceItem['id'],
                'question_responses' => [
                    [
                        'question_id' => $questions[0]['id'],
                        'response' => ['A'],
                    ],
                ],
            ],
        ];
        $itemResponsesReviewResult = $this->getItemService()->review($itemResponses);
        $this->assertEquals($singleChoiceItem['id'], $itemResponsesReviewResult[0]['item_id']);
        $this->assertEquals('right', $itemResponsesReviewResult[0]['result']);
        $questionsResponseReviewResult = $itemResponsesReviewResult[0]['question_responses_review_result'][0];
        $this->assertEquals($questions[0]['id'], $questionsResponseReviewResult['question_id']);
        $this->assertEquals('right', $questionsResponseReviewResult['result']);
        $this->assertEquals(['right', 'none', 'none', 'none'], $questionsResponseReviewResult['response_points_result']);

        $itemResponses[0]['question_responses'][0]['response'] = [];
        $itemResponsesReviewResult = $this->getItemService()->review($itemResponses);
        $this->assertEquals('wrong', $itemResponsesReviewResult[0]['result']);
        $questionsResponseReviewResult = $itemResponsesReviewResult[0]['question_responses_review_result'][0];
        $this->assertEquals('wrong', $questionsResponseReviewResult['result']);
        $this->assertEquals(['none', 'none', 'none', 'none'], $questionsResponseReviewResult['response_points_result']);

        $itemResponses[0]['question_responses'][0]['response'] = ['B'];
        $itemResponsesReviewResult = $this->getItemService()->review($itemResponses);
        $this->assertEquals('wrong', $itemResponsesReviewResult[0]['result']);
        $questionsResponseReviewResult = $itemResponsesReviewResult[0]['question_responses_review_result'][0];
        $this->assertEquals('wrong', $questionsResponseReviewResult['result']);
        $this->assertEquals(['none', 'wrong', 'none', 'none'], $questionsResponseReviewResult['response_points_result']);
    }

    public function testReviewUncertainChoiceItem()
    {
        $uncertainChoiceItem = $this->createItem($this->getUncertainChoiceItemFields());
        $questions = $this->getQuestionDao()->findByItemId($uncertainChoiceItem['id']);
        $itemResponses = [
            [
                'item_id' => $uncertainChoiceItem['id'],
                'question_responses' => [
                    [
                        'question_id' => $questions[0]['id'],
                        'response' => ['A'],
                    ],
                ],
            ],
        ];
        $itemResponsesReviewResult = $this->getItemService()->review($itemResponses);
        $this->assertEquals($uncertainChoiceItem['id'], $itemResponsesReviewResult[0]['item_id']);
        $this->assertEquals('wrong', $itemResponsesReviewResult[0]['result']);
        $questionsResponseReviewResult = $itemResponsesReviewResult[0]['question_responses_review_result'][0];
        $this->assertEquals($questions[0]['id'], $questionsResponseReviewResult['question_id']);
        $this->assertEquals('wrong', $questionsResponseReviewResult['result']);
        $this->assertEquals(['wrong', 'none', 'none', 'none'], $questionsResponseReviewResult['response_points_result']);

        $itemResponses[0]['question_responses'][0]['response'] = [];
        $itemResponsesReviewResult = $this->getItemService()->review($itemResponses);
        $this->assertEquals('wrong', $itemResponsesReviewResult[0]['result']);
        $questionsResponseReviewResult = $itemResponsesReviewResult[0]['question_responses_review_result'][0];
        $this->assertEquals('wrong', $questionsResponseReviewResult['result']);
        $this->assertEquals(['none', 'none', 'none', 'none'], $questionsResponseReviewResult['response_points_result']);

        $itemResponses[0]['question_responses'][0]['response'] = ['A', 'B'];
        $itemResponsesReviewResult = $this->getItemService()->review($itemResponses);
        $this->assertEquals('wrong', $itemResponsesReviewResult[0]['result']);
        $questionsResponseReviewResult = $itemResponsesReviewResult[0]['question_responses_review_result'][0];
        $this->assertEquals('wrong', $questionsResponseReviewResult['result']);
        $this->assertEquals(['wrong', 'right', 'none', 'none'], $questionsResponseReviewResult['response_points_result']);

        $itemResponses[0]['question_responses'][0]['response'] = ['B'];
        $itemResponsesReviewResult = $this->getItemService()->review($itemResponses);
        $this->assertEquals('wrong', $itemResponsesReviewResult[0]['result']);
        $questionsResponseReviewResult = $itemResponsesReviewResult[0]['question_responses_review_result'][0];
        $this->assertEquals('wrong', $questionsResponseReviewResult['result']);
        $this->assertEquals(['none', 'right', 'none', 'none'], $questionsResponseReviewResult['response_points_result']);

        $itemResponses[0]['question_responses'][0]['response'] = ['B', 'D'];
        $itemResponsesReviewResult = $this->getItemService()->review($itemResponses);
        $this->assertEquals('right', $itemResponsesReviewResult[0]['result']);
        $questionsResponseReviewResult = $itemResponsesReviewResult[0]['question_responses_review_result'][0];
        $this->assertEquals('right', $questionsResponseReviewResult['result']);
        $this->assertEquals(['none', 'right', 'none', 'right'], $questionsResponseReviewResult['response_points_result']);
    }

    public function testReviewChoiceItem()
    {
        $choiceItem = $this->createItem($this->getChoiceItemFields());
        $questions = $this->getQuestionDao()->findByItemId($choiceItem['id']);
        $itemResponses = [
            [
                'item_id' => $choiceItem['id'],
                'question_responses' => [
                    [
                        'question_id' => $questions[0]['id'],
                        'response' => ['A'],
                    ],
                ],
            ],
        ];
        $itemResponsesReviewResult = $this->getItemService()->review($itemResponses);
        $this->assertEquals($choiceItem['id'], $itemResponsesReviewResult[0]['item_id']);
        $this->assertEquals('wrong', $itemResponsesReviewResult[0]['result']);
        $questionsResponseReviewResult = $itemResponsesReviewResult[0]['question_responses_review_result'][0];
        $this->assertEquals($questions[0]['id'], $questionsResponseReviewResult['question_id']);
        $this->assertEquals('wrong', $questionsResponseReviewResult['result']);
        $this->assertEquals(['wrong', 'none', 'none', 'none'], $questionsResponseReviewResult['response_points_result']);

        $itemResponses[0]['question_responses'][0]['response'] = [];
        $itemResponsesReviewResult = $this->getItemService()->review($itemResponses);
        $this->assertEquals('wrong', $itemResponsesReviewResult[0]['result']);
        $questionsResponseReviewResult = $itemResponsesReviewResult[0]['question_responses_review_result'][0];
        $this->assertEquals('wrong', $questionsResponseReviewResult['result']);
        $this->assertEquals(['none', 'none', 'none', 'none'], $questionsResponseReviewResult['response_points_result']);

        $itemResponses[0]['question_responses'][0]['response'] = ['A', 'B'];
        $itemResponsesReviewResult = $this->getItemService()->review($itemResponses);
        $this->assertEquals('wrong', $itemResponsesReviewResult[0]['result']);
        $questionsResponseReviewResult = $itemResponsesReviewResult[0]['question_responses_review_result'][0];
        $this->assertEquals('wrong', $questionsResponseReviewResult['result']);
        $this->assertEquals(['wrong', 'right', 'none', 'none'], $questionsResponseReviewResult['response_points_result']);

        $itemResponses[0]['question_responses'][0]['response'] = ['B'];
        $itemResponsesReviewResult = $this->getItemService()->review($itemResponses);
        $this->assertEquals('wrong', $itemResponsesReviewResult[0]['result']);
        $questionsResponseReviewResult = $itemResponsesReviewResult[0]['question_responses_review_result'][0];
        $this->assertEquals('wrong', $questionsResponseReviewResult['result']);
        $this->assertEquals(['none', 'right', 'none', 'none'], $questionsResponseReviewResult['response_points_result']);

        $itemResponses[0]['question_responses'][0]['response'] = ['B', 'D'];
        $itemResponsesReviewResult = $this->getItemService()->review($itemResponses);
        $this->assertEquals('right', $itemResponsesReviewResult[0]['result']);
        $questionsResponseReviewResult = $itemResponsesReviewResult[0]['question_responses_review_result'][0];
        $this->assertEquals('right', $questionsResponseReviewResult['result']);
        $this->assertEquals(['none', 'right', 'none', 'right'], $questionsResponseReviewResult['response_points_result']);
    }

    public function testReviewDetermineItem()
    {
        $determineItem = $this->createItem($this->getDetermineItemFields());
        $questions = $this->getQuestionDao()->findByItemId($determineItem['id']);
        $itemResponses = [
            [
                'item_id' => $determineItem['id'],
                'question_responses' => [
                    [
                        'question_id' => $questions[0]['id'],
                        'response' => ['T'],
                    ],
                ],
            ],
        ];
        $itemResponsesReviewResult = $this->getItemService()->review($itemResponses);
        $this->assertEquals($determineItem['id'], $itemResponsesReviewResult[0]['item_id']);
        $this->assertEquals('right', $itemResponsesReviewResult[0]['result']);
        $questionsResponseReviewResult = $itemResponsesReviewResult[0]['question_responses_review_result'][0];
        $this->assertEquals($questions[0]['id'], $questionsResponseReviewResult['question_id']);
        $this->assertEquals('right', $questionsResponseReviewResult['result']);
        $this->assertEquals(['right', 'none'], $questionsResponseReviewResult['response_points_result']);

        $itemResponses[0]['question_responses'][0]['response'] = [];
        $itemResponsesReviewResult = $this->getItemService()->review($itemResponses);
        $this->assertEquals('wrong', $itemResponsesReviewResult[0]['result']);
        $questionsResponseReviewResult = $itemResponsesReviewResult[0]['question_responses_review_result'][0];
        $this->assertEquals('wrong', $questionsResponseReviewResult['result']);
        $this->assertEquals(['none', 'none'], $questionsResponseReviewResult['response_points_result']);

        $itemResponses[0]['question_responses'][0]['response'] = ['F'];
        $itemResponsesReviewResult = $this->getItemService()->review($itemResponses);
        $this->assertEquals('wrong', $itemResponsesReviewResult[0]['result']);
        $questionsResponseReviewResult = $itemResponsesReviewResult[0]['question_responses_review_result'][0];
        $this->assertEquals('wrong', $questionsResponseReviewResult['result']);
        $this->assertEquals(['none', 'wrong'], $questionsResponseReviewResult['response_points_result']);
    }

    public function testReviewFillItem()
    {
        $fillItem = $this->createItem($this->getFillItemFields());
        $questions = $this->getQuestionDao()->findByItemId($fillItem['id']);
        $itemResponses = [
            [
                'item_id' => $fillItem['id'],
                'question_responses' => [
                    [
                        'question_id' => $questions[0]['id'],
                        'response' => ['这是答案'],
                    ],
                ],
            ],
        ];
        $itemResponsesReviewResult = $this->getItemService()->review($itemResponses);
        $this->assertEquals($fillItem['id'], $itemResponsesReviewResult[0]['item_id']);
        $this->assertEquals('wrong', $itemResponsesReviewResult[0]['result']);
        $questionsResponseReviewResult = $itemResponsesReviewResult[0]['question_responses_review_result'][0];
        $this->assertEquals($questions[0]['id'], $questionsResponseReviewResult['question_id']);
        $this->assertEquals('wrong', $questionsResponseReviewResult['result']);
        $this->assertEquals(['wrong', 'none'], $questionsResponseReviewResult['response_points_result']);

        $itemResponses[0]['question_responses'][0]['response'] = [];
        $itemResponsesReviewResult = $this->getItemService()->review($itemResponses);
        $this->assertEquals('wrong', $itemResponsesReviewResult[0]['result']);
        $questionsResponseReviewResult = $itemResponsesReviewResult[0]['question_responses_review_result'][0];
        $this->assertEquals('wrong', $questionsResponseReviewResult['result']);
        $this->assertEquals(['none', 'none'], $questionsResponseReviewResult['response_points_result']);

        $itemResponses[0]['question_responses'][0]['response'] = ['李白'];
        $itemResponsesReviewResult = $this->getItemService()->review($itemResponses);
        $this->assertEquals('wrong', $itemResponsesReviewResult[0]['result']);
        $questionsResponseReviewResult = $itemResponsesReviewResult[0]['question_responses_review_result'][0];
        $this->assertEquals('wrong', $questionsResponseReviewResult['result']);
        $this->assertEquals(['right', 'none'], $questionsResponseReviewResult['response_points_result']);

        $itemResponses[0]['question_responses'][0]['response'] = ['李白', '易安居士'];
        $itemResponsesReviewResult = $this->getItemService()->review($itemResponses);
        $this->assertEquals('wrong', $itemResponsesReviewResult[0]['result']);
        $questionsResponseReviewResult = $itemResponsesReviewResult[0]['question_responses_review_result'][0];
        $this->assertEquals('wrong', $questionsResponseReviewResult['result']);
        $this->assertEquals(['right', 'wrong'], $questionsResponseReviewResult['response_points_result']);

        $itemResponses[0]['question_responses'][0]['response'] = ['李白', '青莲居士'];
        $itemResponsesReviewResult = $this->getItemService()->review($itemResponses);
        $this->assertEquals('right', $itemResponsesReviewResult[0]['result']);
        $questionsResponseReviewResult = $itemResponsesReviewResult[0]['question_responses_review_result'][0];
        $this->assertEquals('right', $questionsResponseReviewResult['result']);
        $this->assertEquals(['right', 'right'], $questionsResponseReviewResult['response_points_result']);
    }

    public function testReviewEssayItem()
    {
        $essayItem = $this->createItem($this->getEssayItemFields());
        $questions = $this->getQuestionDao()->findByItemId($essayItem['id']);

        $itemResponses = [
            [
                'item_id' => $essayItem['id'],
                'question_responses' => [
                    [
                        'question_id' => $questions[0]['id'],
                        'response' => ['这是答案'],
                    ],
                ],
            ],
        ];
        $itemResponsesReviewResult = $this->getItemService()->review($itemResponses);
        $this->assertEquals($essayItem['id'], $itemResponsesReviewResult[0]['item_id']);
        $this->assertEquals('none', $itemResponsesReviewResult[0]['result']);
        $questionsResponseReviewResult = $itemResponsesReviewResult[0]['question_responses_review_result'][0];
        $this->assertEquals($questions[0]['id'], $questionsResponseReviewResult['question_id']);
        $this->assertEquals('none', $questionsResponseReviewResult['result']);
        $this->assertEmpty($questionsResponseReviewResult['response_points_result']);
    }

    public function testExportItems()
    {
        $this->mockItemBankService();
        $this->createItem($this->getSingleChoiceItemFields());
        $this->createItem($this->getUncertainChoiceItemFields());
        $this->createItem($this->getChoiceItemFields());
        $this->createItem($this->getDetermineItemFields());
        $this->createItem($this->getFillItemFields());
        $this->createItem($this->getEssayItemFields());
        $this->createItem($this->getMaterialItemFields());

        $result = $this->getItemService()->exportItems(1, [], '/tmp/export.docx', '');
        $this->assertTrue($result);
    }

    protected function createItem($item)
    {
        $questions = $item['questions'];
        unset($item['questions']);
        unset($item['attachments']);
        $item['question_num'] = count($questions);
        $item['created_user_id'] = $item['updated_user_id'] = 1;
        $item['material'] = $item['material'] ?: $questions[0]['stem'];
        $item = $this->getItemDao()->create($item);
        foreach ($questions as &$question) {
            $question['item_id'] = $item['id'];
            $question['created_user_id'] = $question['updated_user_id'] = 1;
            unset($question['attachments']);
        }

        $this->getQuestionDao()->batchCreate($questions);

        return $item;
    }

    protected function getSingleChoiceItemFields()
    {
        $fields = $this->getItemFields();
        $fields['type'] = 'single_choice';
        $fields['questions'][0]['response_points'] = $this->getSingleChoiceResponsePointsField();
        $fields['questions'][0]['answer'] = ['A'];
        $fields['questions'][0]['answer_mode'] = 'single_choice';

        return $fields;
    }

    protected function getUncertainChoiceItemFields()
    {
        $fields = $this->getItemFields();
        $fields['type'] = 'uncertain_choice';
        $fields['questions'][0]['response_points'] = $this->getChoiceResponsePointsField();
        $fields['questions'][0]['answer'] = ['B', 'D'];
        $fields['questions'][0]['answer_mode'] = 'uncertain_choice';

        return $fields;
    }

    protected function getChoiceItemFields()
    {
        $fields = $this->getItemFields();
        $fields['type'] = 'choice';
        $fields['questions'][0]['response_points'] = $this->getChoiceResponsePointsField();
        $fields['questions'][0]['answer'] = ['B', 'D'];
        $fields['questions'][0]['answer_mode'] = 'choice';

        return $fields;
    }

    protected function getDetermineItemFields()
    {
        $fields = $this->getItemFields();
        $fields['type'] = 'determine';
        $fields['questions'][0]['response_points'] = $this->getDetermineResponsePointField();
        $fields['questions'][0]['answer'] = ['T'];
        $fields['questions'][0]['answer_mode'] = 'true_false';

        return $fields;
    }

    protected function getFillItemFields()
    {
        $fields = $this->getItemFields();
        $fields['type'] = 'fill';
        $fields['questions'][0]['stem'] = '诗仙[[]], 号[[]]';
        $fields['questions'][0]['response_points'] = $this->getFillResponsePointField();
        $fields['questions'][0]['answer'] = ['李白', '谪仙人|青莲居士'];
        $fields['questions'][0]['answer_mode'] = 'text';

        return $fields;
    }

    protected function getEssayItemFields()
    {
        $fields = $this->getItemFields();
        $fields['type'] = 'essay';
        $fields['questions'][0]['response_points'] = [];
        $fields['questions'][0]['answer'] = ['<p>这是答案</p>'];
        $fields['questions'][0]['answer_mode'] = 'rich_text';

        return $fields;
    }

    protected function getMaterialItemFields()
    {
        $fields = $this->getItemFields();
        $fields['type'] = 'material';
        $fields['material'] = '<p>这是材料题材料</p>';
        $fields['questions'][0]['response_points'] = $this->getSingleChoiceResponsePointsField();
        $fields['questions'][0]['answer'] = ['B'];
        $fields['questions'][0]['answer_mode'] = 'single_choice';

        $question = $fields['questions'][0];
        $question['response_points'] = $this->getChoiceResponsePointsField();
        $question['answer'] = ['A', 'C'];
        $question['answer_mode'] = 'choice';
        $fields['questions'][] = $question;

        $question['response_points'] = $this->getDetermineResponsePointField();
        $question['answer'] = ['F'];
        $question['answer_mode'] = 'true_false';
        $fields['questions'][] = $question;

        $question['response_points'] = $this->getFillResponsePointField();
        $question['stem'] = '诗仙[[]], 号[[]]';
        $question['answer'] = ['李白', '谪仙人|青莲居士'];
        $question['answer_mode'] = 'text';
        $fields['questions'][] = $question;

        $question['response_points'] = [['rich_text' => []]];
        $question['stem'] = '<p>这是题干</p>';
        $question['answer'] = ['<p>这是答案</p>'];
        $question['answer_mode'] = 'rich_text';
        $fields['questions'][] = $question;

        return $fields;
    }

    protected function getItemFields()
    {
        return [
            'bank_id' => '1',
            'category_id' => '1',
            'difficulty' => 'normal',
            'material' => '',
            'analysis' => '<p>这是解析</p>',
            'type' => '',
            'attachments' => [],
            'questions' => [
                [
                    'stem' => '<p>这是题干</p>',
                    'seq' => '1',
                    'score' => '2.0',
                    'response_points' => [],
                    'answer' => [],
                    'analysis' => '<p>这是解析</p>',
                    'answer_mode' => '',
                    'attachments' => [],
                ],
            ],
        ];
    }

    protected function getSingleChoiceResponsePointsField()
    {
        return [
            [
                'radio' => [
                    'val' => 'A',
                    'text' => '选项A',
                ],
            ],
            [
                'radio' => [
                    'val' => 'B',
                    'text' => '选项B',
                ],
            ],
            [
                'radio' => [
                    'val' => 'C',
                    'text' => '选项C',
                ],
            ],
            [
                'radio' => [
                    'val' => 'D',
                    'text' => '选项D',
                ],
            ],
        ];
    }

    protected function getChoiceResponsePointsField()
    {
        return [
            [
                'checkbox' => [
                    'val' => 'A',
                    'text' => '选项A',
                ],
            ],
            [
                'checkbox' => [
                    'val' => 'B',
                    'text' => '选项B',
                ],
            ],
            [
                'checkbox' => [
                    'val' => 'C',
                    'text' => '选项C',
                ],
            ],
            [
                'checkbox' => [
                    'val' => 'D',
                    'text' => '选项D',
                ],
            ],
        ];
    }

    protected function getDetermineResponsePointField()
    {
        return [
            [
                'radio' => [
                    'val' => 'T',
                    'text' => '正确',
                ],
            ],
            [
                'radio' => [
                    'val' => 'F',
                    'text' => '错误',
                ],
            ],
        ];
    }

    protected function getFillResponsePointField()
    {
        return [
            ['text' => []],
            ['text' => []],
        ];
    }

    protected function mockItemBankService()
    {
        $this->mockObjectIntoBiz('ItemBank:ItemBank:ItemBankService', [
            [
                'functionName' => 'getItemBank',
                'returnValue' => ['id' => 1],
            ],
            [
                'functionName' => 'updateItemNumAndQuestionNum',
                'returnValue' => 1,
            ],
        ]);
    }

    protected function mockItemCategoryService()
    {
        $this->mockObjectIntoBiz('ItemBank:Item:ItemCategoryService', [
            [
                'functionName' => 'getItemCategory',
                'returnValue' => ['id' => 1, 'bank_id' => 1],
            ],
            [
                'functionName' => 'updateItemNumAndQuestionNum',
                'returnValue' => 1,
            ],
            [
                'functionName' => 'buildItemNumAndQuestionNumBybankId',
                'returnValue' => 1,
            ]
        ]);
    }

    /**
     * @return ItemService
     */
    protected function getItemService()
    {
        return $this->biz->service('ItemBank:Item:ItemService');
    }

    /**
     * @return ItemDao
     */
    protected function getItemDao()
    {
        return $this->biz->dao('ItemBank:Item:ItemDao');
    }

    /**
     * @return QuestionDao
     */
    protected function getQuestionDao()
    {
        return $this->biz->dao('ItemBank:Item:QuestionDao');
    }
}
