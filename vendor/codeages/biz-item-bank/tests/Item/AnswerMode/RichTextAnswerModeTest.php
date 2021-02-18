<?php

namespace Tests\Item\AnswerMode;

use Codeages\Biz\ItemBank\Item\AnswerMode\RichTextAnswerMode;
use Tests\IntegrationTestCase;

class RichTextAnswerModeTest extends IntegrationTestCase
{
    public function testReview()
    {
        $result = $this->getAnswerMode()->review([['rich_text' => []]], ['参考答案'], ['回答']);

        $this->assertEquals('none', $result['result']);
    }

    public function testGetAnswerSceneQuestionReport()
    {
        $question = [
            'id' => 1,
            'item_id' => 1,
            'response_points' => [
                [
                    'rich_text' => [],
                ]
            ]
        ];
        $questionReports = [
            ['status' => 'no_answer', 'response' => ['']],
            ['status' => 'no_answer', 'response' => ['']],
        ];

        $result = $this->getAnswerMode()->getAnswerSceneQuestionReport($question, $questionReports);
        
        $this->assertEquals(0, $result['right_num']);
        $this->assertEquals(0, $result['wrong_num']);
        $this->assertEquals(2, $result['no_answer_num']);
        $this->assertEquals(0, $result['part_right_num']);
        $this->assertEquals([], $result['response_points_report']);
    }

    protected function getAnswerMode()
    {
        return new RichTextAnswerMode($this->biz);
    }
}
