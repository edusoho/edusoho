<?php

namespace Tests\Item\Wrapper;

use Codeages\Biz\ItemBank\Item\Wrapper\ItemWrapper;
use Tests\IntegrationTestCase;

class ItemWrapperTest extends IntegrationTestCase
{
    public function testWrap()
    {
        $itemWrapper = $this->getItemWrapper();
        $item = $itemWrapper->wrap($this->getItem(), true);

        $this->assertTrue(isset($item['analysis']));
        $this->assertEmpty($item['material']);

        $item = $itemWrapper->wrap($this->getItem(), false);
        $this->assertFalse(isset($item['analysis']));
        $this->assertEmpty($item['material']);

        $item = $this->getItem();
        $item['type'] = 'material';
        $item = $itemWrapper->wrap($item, false);
        $this->assertNotEmpty($item['material']);
    }

    protected function getItemWrapper()
    {
        return new ItemWrapper($this->biz);
    }

    protected function getItem()
    {
        return [
            'id' => 1,
            'bank_id' => 1,
            'category_id' => 1,
            'difficulty' => 'normal',
            'material' => '<p>这是题干</p>',
            'analysis' => '<p>这是解析</p>',
            'type' => 'single_choice',
            'questions' => [
                [
                    'id' => 1,
                    'stem' => '<p>这是题干</p>',
                    'seq' => '1',
                    'score' => '2.0',
                    'response_points' => [
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
                    ],
                    'answer' => ['A'],
                    'analysis' => '<p>这是解析</p>',
                    'answer_mode' => 'single_choice',
                ],
            ],
        ];
    }
}
