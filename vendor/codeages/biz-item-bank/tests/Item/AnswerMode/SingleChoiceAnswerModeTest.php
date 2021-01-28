<?php

namespace Tests\Item\AnswerMode;

use Codeages\Biz\ItemBank\Item\AnswerMode\SingleChoiceAnswerMode;
use Tests\IntegrationTestCase;

class SingleChoiceAnswerModeTest extends IntegrationTestCase
{
    /**
     * @expectedException \Codeages\Biz\ItemBank\Item\Exception\QuestionException
     */
    public function testValidate_ResponsePointsInvalid_ThrowException()
    {
        $this->getAnswerMode()->validate([
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
                'checkbox' => [
                    'val' => 'C',
                    'text' => '选项C',
                ],
            ],
        ], ['A']);
    }

    /**
     * @expectedException  \Codeages\Biz\ItemBank\Item\Exception\QuestionException
     */
    public function testValidate_ResponsePointsLessThanMin_ThrowException()
    {
        $this->getAnswerMode()->validate([
            [
                'radio' => [
                    'val' => 'A',
                    'text' => '选项A',
                ],
            ],
        ], ['A']);
    }

    /**
     * @expectedException  \Codeages\Biz\ItemBank\Item\Exception\QuestionException
     */
    public function testValidate_AnswerInvalid_ThrowException()
    {
        $this->getAnswerMode()->validate([
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
        ], ['D']);
    }

    /**
     * @expectedException  \Codeages\Biz\ItemBank\Item\Exception\QuestionException
     */
    public function testValidate_AnswerMoreThanOne_ThrowException()
    {
        $this->getAnswerMode()->validate([
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
        ], ['A', 'B']);
    }

    public function testFilter()
    {
        $responsePoints = $this->getAnswerMode()->filter([[
            'radio' => [
                'id' => 1,
                'val' => 'A',
                'text' => '选项A',
            ],
        ]]);
        $this->assertEquals([['radio' => ['val' => 'A', 'text' => '选项A']]], $responsePoints);
    }

    public function testReview()
    {
        $responsePoints = [
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
        ];
        $answer = ['A'];

        $result = $this->getAnswerMode()->review($responsePoints, $answer, []);
        $this->assertEquals('wrong', $result['result']);
        $this->assertEquals(['none', 'none', 'none'], $result['response_points_result']);

        $result = $this->getAnswerMode()->review($responsePoints, $answer, ['A']);
        $this->assertEquals('right', $result['result']);
        $this->assertEquals(['right', 'none', 'none'], $result['response_points_result']);

        $result = $this->getAnswerMode()->review($responsePoints, $answer, ['B']);
        $this->assertEquals('wrong', $result['result']);
        $this->assertEquals(['none', 'wrong', 'none'], $result['response_points_result']);
    }
    
    public function testGetAnswerSceneQuestionReport()
    {
        $question = [
            'id' => 1,
            'item_id' => 1,
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
            ]
        ];
        $questionReports = [
            ['status' => 'right', 'response' => ['A']],
            ['status' => 'wrong', 'response' => ['A']],
        ];

        $result = $this->getAnswerMode()->getAnswerSceneQuestionReport($question, $questionReports);

        $this->assertEquals(1, $result['right_num']);
        $this->assertEquals(1, $result['wrong_num']);
        $this->assertEquals(0, $result['no_answer_num']);
        $this->assertEquals(0, $result['part_right_num']);
        $this->assertEquals(2, $result['response_points_report'][0]['num']);
        $this->assertEquals(0, $result['response_points_report'][1]['num']);
        $this->assertEquals(0, $result['response_points_report'][2]['num']);
    }

    protected function getAnswerMode()
    {
        return new SingleChoiceAnswerMode($this->biz);
    }
}
