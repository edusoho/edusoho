<?php

namespace Tests\Item\AnswerMode;

use Codeages\Biz\ItemBank\Item\AnswerMode\TextAnswerMode;
use Tests\IntegrationTestCase;

class TextAnswerModeTest extends IntegrationTestCase
{
    /**
     * @expectedException \Codeages\Biz\ItemBank\Item\Exception\QuestionException
     */
    public function testValidate_ResponsePointsInvalid_ThrowException()
    {
        $this->getAnswerMode()->validate([
            [
                'text' => [],
            ],
            [
            ],
        ], ['李白', '青莲居士|谪仙人']);
    }

    /**
     * @expectedException \Codeages\Biz\ItemBank\Item\Exception\QuestionException
     */
    public function testValidate_AnswerNotMatchResponsePoints_ThrowException()
    {
        $this->getAnswerMode()->validate([
            [
                'text' => [],
            ],
            [
                'text' => [],
            ],
        ], ['李白']);
    }

    public function testReview()
    {
        $responsePoints = [
            [
                'text' => [],
            ],
            [
                'text' => [],
            ],
        ];
        $answer = ['李白', '青莲居士|谪仙人'];

        $result = $this->getAnswerMode()->review($responsePoints, $answer, []);
        $this->assertEquals('wrong', $result['result']);
        $this->assertEquals(['none', 'none'], $result['response_points_result']);

        $result = $this->getAnswerMode()->review($responsePoints, $answer, ['李白']);
        $this->assertEquals('wrong', $result['result']);
        $this->assertEquals(['right', 'none'], $result['response_points_result']);

        $result = $this->getAnswerMode()->review($responsePoints, $answer, ['青莲居士']);
        $this->assertEquals('wrong', $result['result']);
        $this->assertEquals(['wrong', 'none'], $result['response_points_result']);

        $result = $this->getAnswerMode()->review($responsePoints, $answer, ['李白', '青莲居士']);
        $this->assertEquals('right', $result['result']);
        $this->assertEquals(['right', 'right'], $result['response_points_result']);
    }

    public function testGetAnswerSceneQuestionReport()
    {
        $question = [
            'id' => 1,
            'item_id' => 1,
            'response_points' => [
                [
                    'text' => [],
                ],
                [
                    'text' => []
                ]
            ],
            'answer' => ['张三|李四', '男'],
        ];
        $questionReports = [
            ['status' => 'part_right', 'response' => ['张三', '女']],
            ['status' => 'part_right', 'response' => ['李四', '男']],
        ];

        $result = $this->getAnswerMode()->getAnswerSceneQuestionReport($question, $questionReports);

        $this->assertEquals(0, $result['right_num']);
        $this->assertEquals(0, $result['wrong_num']);
        $this->assertEquals(0, $result['no_answer_num']);
        $this->assertEquals(2, $result['part_right_num']);
        $this->assertEquals(2, $result['response_points_report'][0]['num']);
        $this->assertEquals(1, $result['response_points_report'][1]['num']);
    }

    protected function getAnswerMode()
    {
        return new TextAnswerMode($this->biz);
    }
}
