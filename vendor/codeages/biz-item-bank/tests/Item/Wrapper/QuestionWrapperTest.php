<?php

namespace Tests\Item\Wrapper;

use Codeages\Biz\ItemBank\Item\Wrapper\QuestionWrapper;
use Tests\IntegrationTestCase;

class QuestionWrapperTest extends IntegrationTestCase
{
    public function testWrap()
    {
        $question = $this->getQuestionWrapper()->wrap($this->getQuestion(), true);
        $this->assertTrue(isset($question['answer']));
        $this->assertTrue(isset($question['analysis']));

        $question = $this->getQuestionWrapper()->wrap($this->getQuestion(), false);
        $this->assertFalse(isset($question['answer']));
        $this->assertFalse(isset($question['analysis']));
    }

    protected function getQuestionWrapper()
    {
        return new QuestionWrapper($this->biz);
    }

    protected function getQuestion()
    {
        return [
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
        ];
    }
}
