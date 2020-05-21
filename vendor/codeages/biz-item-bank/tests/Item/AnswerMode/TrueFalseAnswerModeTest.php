<?php

namespace Tests\Item\AnswerMode;

use Codeages\Biz\ItemBank\Item\AnswerMode\TrueFalseAnswerMode;
use Tests\IntegrationTestCase;

class TrueFalseAnswerModeTest extends IntegrationTestCase
{
    /**
     * @expectedException \Codeages\Biz\ItemBank\Item\Exception\QuestionException
     */
    public function testValidate_ResponsePointsInvalid_ThrowException()
    {
        $this->getAnswerMode()->validate([
            [
                'checkbox' => [
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
        ], ['T']);
    }

    /**
     * @expectedException \Codeages\Biz\ItemBank\Item\Exception\QuestionException
     */
    public function testValidate_ResponsePointsNotTwo_ThrowException()
    {
        $this->getAnswerMode()->validate([
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
            [
                'radio' => [
                    'val' => 'F',
                    'text' => '错误',
                ],
            ],
        ], ['T']);
    }

    /**
     * @expectedException \Codeages\Biz\ItemBank\Item\Exception\QuestionException
     */
    public function testValidate_AnswerNotOne_ThrowException()
    {
        $this->getAnswerMode()->validate([
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
        ], ['T', 'F']);
    }

    /**
     * @expectedException \Codeages\Biz\ItemBank\Item\Exception\QuestionException
     */
    public function testValidate_AnswerInvalid_ThrowException()
    {
        $this->getAnswerMode()->validate([
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
        ], ['N']);
    }

    public function testReview()
    {
        $responsePoints = [
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
        $answer = ['T'];

        $result = $this->getAnswerMode()->review($responsePoints, $answer, []);
        $this->assertEquals('wrong', $result['result']);
        $this->assertEquals(['none', 'none'], $result['response_points_result']);

        $result = $this->getAnswerMode()->review($responsePoints, $answer, ['T']);
        $this->assertEquals('right', $result['result']);
        $this->assertEquals(['right', 'none'], $result['response_points_result']);

        $result = $this->getAnswerMode()->review($responsePoints, $answer, ['F']);
        $this->assertEquals('wrong', $result['result']);
        $this->assertEquals(['none', 'wrong'], $result['response_points_result']);
    }

    public function testGetAnswerSceneQuestionReport()
    {
        $question = [
            'response_points' => [
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
                ]
            ]
        ];
        $questionReports = [
            ['status' => 'right', 'response' => ['T']],
            ['status' => 'wrong', 'response' => ['F']],
        ];

        $result = $this->getAnswerMode()->getAnswerSceneQuestionReport($question, $questionReports);

        $this->assertEquals(1, $result['right_num']);
        $this->assertEquals(1, $result['wrong_num']);
        $this->assertEquals(0, $result['no_answer_num']);
        $this->assertEquals(0, $result['part_right_num']);
        $this->assertEquals(1, $result['response_points_report'][0]['num']);
        $this->assertEquals(1, $result['response_points_report'][1]['num']);
    }

    protected function getAnswerMode()
    {
        return new TrueFalseAnswerMode($this->biz);
    }
}
