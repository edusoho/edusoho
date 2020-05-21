<?php

namespace Tests\Item\AnswerMode;

use Codeages\Biz\ItemBank\Item\AnswerMode\ChoiceAnswerMode;
use Tests\IntegrationTestCase;

class ChoiceAnswerModeTest extends IntegrationTestCase
{
    /**
     * @expectedException \Codeages\Biz\ItemBank\Item\Exception\QuestionException
     */
    public function testValidate_ResponsePointsInvalid_ThrowException()
    {
        $this->getAnswerMode()->validate([
            [
                'checkbox' => [
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
        ], ['A', 'B']);
    }

    /**
     * @expectedException \Codeages\Biz\ItemBank\Item\Exception\QuestionException
     */
    public function testValidate_ResponsePointsLessThanMin_ThrowException()
    {
        $this->getAnswerMode()->validate([
            [
                'checkbox' => [
                    'val' => 'A',
                    'text' => '选项A',
                ],
            ],
        ], ['A', 'B']);
    }

    /**
     * @expectedException \Codeages\Biz\ItemBank\Item\Exception\QuestionException
     */
    public function testValidate_AnswerInvalid_ThrowException()
    {
        $this->getAnswerMode()->validate([
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
        ], ['A', 'D']);
    }

    /**
     * @expectedException \Codeages\Biz\ItemBank\Item\Exception\QuestionException
     */
    public function testValidate_AnswerLessThanMin_ThrowException()
    {
        $this->getAnswerMode()->validate([
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
        ], ['A']);
    }

    public function testFilter()
    {
        $responsePoints = $this->getAnswerMode()->filter([[
            'checkbox' => [
                'id' => 1,
                'val' => 'A',
                'text' => '选项A',
            ],
        ]]);
        $this->assertEquals([['checkbox' => ['val' => 'A', 'text' => '选项A']]], $responsePoints);
    }

    public function testReview()
    {
        $responsePoints = [
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
        ];
        $answer = ['A', 'B'];

        $result = $this->getAnswerMode()->review($responsePoints, $answer, []);
        $this->assertEquals('wrong', $result['result']);
        $this->assertEquals(['none', 'none', 'none'], $result['response_points_result']);

        $result = $this->getAnswerMode()->review($responsePoints, $answer, ['A']);
        $this->assertEquals('wrong', $result['result']);
        $this->assertEquals(['right', 'none', 'none'], $result['response_points_result']);

        $result = $this->getAnswerMode()->review($responsePoints, $answer, ['B']);
        $this->assertEquals('wrong', $result['result']);
        $this->assertEquals(['none', 'right', 'none'], $result['response_points_result']);

        $result = $this->getAnswerMode()->review($responsePoints, $answer, ['C']);
        $this->assertEquals('wrong', $result['result']);
        $this->assertEquals(['none', 'none', 'wrong'], $result['response_points_result']);

        $result = $this->getAnswerMode()->review($responsePoints, $answer, ['A', 'B']);
        $this->assertEquals('right', $result['result']);
        $this->assertEquals(['right', 'right', 'none'], $result['response_points_result']);

        $result = $this->getAnswerMode()->review($responsePoints, $answer, ['A', 'C']);
        $this->assertEquals('wrong', $result['result']);
        $this->assertEquals(['right', 'none', 'wrong'], $result['response_points_result']);
    }

    public function testGetAnswerSceneQuestionReport()
    {
        $question = [
            'response_points' => [
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
            ]
        ];
        $questionReports = [
            ['status' => 'right', 'response' => ['A']],
            ['status' => 'right', 'response' => ['B']],
        ];

        $result = $this->getAnswerMode()->getAnswerSceneQuestionReport($question, $questionReports);

        $this->assertEquals(2, $result['right_num']);
        $this->assertEquals(0, $result['wrong_num']);
        $this->assertEquals(0, $result['no_answer_num']);
        $this->assertEquals(0, $result['part_right_num']);
        $this->assertEquals(1, $result['response_points_report'][0]['num']);
        $this->assertEquals(1, $result['response_points_report'][1]['num']);
        $this->assertEquals(0, $result['response_points_report'][2]['num']);
    }

    protected function getAnswerMode()
    {
        return new ChoiceAnswerMode($this->biz);
    }
}
