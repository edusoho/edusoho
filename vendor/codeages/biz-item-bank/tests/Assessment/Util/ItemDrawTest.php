<?php

namespace Tests\Assessment\Util;

use Codeages\Biz\ItemBank\Item\Dao\ItemDao;
use Codeages\Biz\ItemBank\Item\Dao\QuestionDao;
use Tests\IntegrationTestCase;

class ItemDrawTest extends IntegrationTestCase
{
    public function testDrawItems()
    {
        $this->mockItem(['type' => 'choice']);
        $this->mockItem(['type' => 'essay']);
        $this->mockItem(['type' => 'fill']);
        $this->mockItem(['type' => 'uncertain_choice']);
        $this->mockItem(['type' => 'material']);

        $range = ['bank_id' => 1];
        $sections = [
            [
                'conditions' => ['item_types' => ['essay']],
                'item_count' => 1,
            ],
            [
                'conditions' => ['item_types' => ['choice']],
                'item_count' => 1,
            ],
        ];

        $result = $this->getItemDrawHelper()->drawItems($range, $sections);

        $this->assertEquals('essay', $result[0]['items'][0]['type']);
        $this->assertEquals(1, count($result[0]['items']));
        $this->assertEquals('choice', $result[1]['items'][0]['type']);
        $this->assertEquals(1, count($result[0]['items']));
    }

    public function testDrawItems_whenFilterDifficulty_thenReturnFixedDifficulty()
    {
        $this->mockItem(['type' => 'choice']);
        $this->mockItem(['type' => 'essay']);
        $this->mockItem(['type' => 'fill']);
        $this->mockItem(['type' => 'choice', 'difficulty' => 'difficulty']);
        $this->mockItem(['type' => 'essay', 'difficulty' => 'difficulty']);

        $range = ['bank_id' => 1];
        $sections = [
            [
                'conditions' => ['item_types' => ['essay'], 'distribution' => ['difficulty' => 100]],
                'item_count' => 1,
            ],
            [
                'conditions' => ['item_types' => ['choice'], 'distribution' => ['difficulty' => 100]],
                'item_count' => 1,
            ],
        ];

        $result = $this->getItemDrawHelper()->drawItems($range, $sections);

        $this->assertEquals('essay', $result[0]['items'][0]['type']);
        $this->assertEquals('difficulty', $result[0]['items'][0]['difficulty']);
        $this->assertEquals(1, count($result[0]['items']));
        $this->assertEquals('choice', $result[1]['items'][0]['type']);
        $this->assertEquals('difficulty', $result[1]['items'][0]['difficulty']);
        $this->assertEquals(1, count($result[0]['items']));
    }

    /**
     * @expectedException \Codeages\Biz\ItemBank\Item\Exception\ItemException
     */
    public function testDrawItems_whenItemMiss_thenReturnMiss()
    {
        $range = ['bank_id' => 1];
        $sections = [
            [
                'conditions' => ['item_types' => ['essay'], 'distribution' => ['difficulty' => 100]],
                'item_count' => 1,
            ],
            [
                'conditions' => ['item_types' => ['choice'], 'distribution' => ['difficulty' => 100]],
                'item_count' => 1,
            ],
        ];

        $result = $this->getItemDrawHelper()->drawItems($range, $sections);
    }

    /**
     * @expectedException \Codeages\Biz\ItemBank\Item\Exception\ItemException
     */
    public function testDrawItems_whenFilterItemDifficulty_thenReturnMiss()
    {
        $this->mockItem(['type' => 'choice', 'difficulty' => 'difficulty']);
        $this->mockItem(['type' => 'essay', 'difficulty' => 'difficulty']);

        $range = ['bank_id' => 1, 'difficulty' => 'simple'];
        $sections = [
            [
                'conditions' => ['item_types' => ['essay']],
                'item_count' => 1,
            ],
            [
                'conditions' => ['item_types' => ['choice']],
                'item_count' => 1,
            ],
        ];

        $result = $this->getItemDrawHelper()->drawItems($range, $sections);
    }

    public function mockItem($item = [], $question = [])
    {
        $default = [
            'bank_id' => 1,
            'category_id' => '1',
            'difficulty' => 'normal',
            'material' => '',
            'analysis' => '<p>这是解析</p>',
            'type' => 'single_choice',
            'question_num' => 1,
            'created_user_id' => 1,
            'updated_user_id' => 1,
        ];
        $item = array_merge($default, $item);
        $item = $this->getItemDao()->create($item);
        $defaultQuestion = [
            'item_id' => $item['id'],
            'stem' => '<p>这是题干</p>',
            'seq' => '1',
            'score' => '2.0',
            'answer' => ['rp1'],
            'analysis' => '<p>这是解析</p>',
            'answer_mode' => 'single_choice',
            'created_user_id' => 1,
            'updated_user_id' => 1,
            'response_points' => [
                [
                    'radio' => [
                        'val' => 'rp1',
                        'text' => '<p>选项A</p>',
                    ],
                ],
                [
                    'radio' => [
                        'val' => 'rp2',
                        'text' => '<p>选项B</p>',
                    ],
                ],
                [
                    'radio' => [
                        'val' => 'rp3',
                        'text' => '<p>选项C</p>',
                    ],
                ],
                [
                    'radio' => [
                        'val' => 'rp4',
                        'text' => '<p>选项D</p>',
                    ],
                ],
            ],
        ];

        $question = array_merge($defaultQuestion, $question);
        $question = $this->getQuestionDao()->create($question);
        $item['questions'][] = $question;

        return $item;
    }

    protected function getItemDrawHelper()
    {
        return $this->biz['item_draw_helper'];
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
