<?php

namespace Tests\Assessment\ScoreRule;

use Tests\IntegrationTestCase;

class ScoreRuleProcessorTest extends IntegrationTestCase
{
    public function testReview_whenQuestionRight_thenReturnRight()
    {
        $questionResult = [
            'question_id' => 1,
            'result' => 'right',
            'response_points_result' => [
                'right',
                'none',
                'none',
                'none',
            ],
        ];
        $rules = [
            ['name' => 'all_right', 'score' => 2],
            ['name' => 'no_answer', 'score' => 0],
            ['name' => 'wrong', 'score' => 0],
        ];
        $result = $this->getProcessor()->review($questionResult, $rules);

        $this->assertEquals(['status' => 'right', 'score' => 2], $result);
    }

    public function testReview_whenQuestionPartRight_thenReturnWrong()
    {
        $questionResult = [
            'question_id' => 1,
            'result' => 'wrong',
            'response_points_result' => [
                'right',
                'none',
                'none',
                'none',
            ],
        ];
        $rules = [
            ['name' => 'all_right', 'score' => 2],
            ['name' => 'part_right', 'score' => 1],
            ['name' => 'no_answer', 'score' => 0],
            ['name' => 'wrong', 'score' => 0],
        ];
        $result = $this->getProcessor()->review($questionResult, $rules);

        $this->assertEquals(['status' => 'part_right', 'score' => 1], $result);
    }

    public function testReview_whenQuestionNoAnswer_thenReturnNoAnswer()
    {
        $questionResult = [
            'question_id' => 1,
            'result' => 'wrong',
            'response_points_result' => [
                'none',
                'none',
                'none',
                'none',
            ],
        ];
        $rules = [
            ['name' => 'all_right', 'score' => 2],
            ['name' => 'part_right', 'score' => 1],
            ['name' => 'no_answer', 'score' => 0],
            ['name' => 'wrong', 'score' => 0],
        ];
        $result = $this->getProcessor()->review($questionResult, $rules);

        $this->assertEquals(['status' => 'no_answer', 'score' => 0], $result);
    }

    public function testReview_whenQuestionWrong_thenReturnWrong()
    {
        $questionResult = [
            'question_id' => 1,
            'result' => 'wrong',
            'response_points_result' => [
                'wrong',
                'none',
                'none',
                'none',
            ],
        ];
        $rules = [
            ['name' => 'all_right', 'score' => 2],
            ['name' => 'part_right', 'score' => 1],
            ['name' => 'no_answer', 'score' => 0],
            ['name' => 'wrong', 'score' => 0],
        ];
        $result = $this->getProcessor()->review($questionResult, $rules);

        $this->assertEquals(['status' => 'wrong', 'score' => 0], $result);
    }

    public function testReview_whenQuestionNoneResult_thenReturnReviewing()
    {
        $questionResult = [
            'question_id' => 1,
            'result' => 'none',
            'response_points_result' => [
                'none',
                'none',
                'none',
                'none',
            ],
        ];
        $rules = [
            ['name' => 'all_right', 'score' => 2],
            ['name' => 'part_right', 'score' => 1],
            ['name' => 'no_answer', 'score' => 0],
            ['name' => 'wrong', 'score' => 0],
        ];
        $result = $this->getProcessor()->review($questionResult, $rules);

        $this->assertEquals(['status' => 'reviewing', 'score' => 0], $result);
    }

    public function testProcessRule()
    {
        $question = [
            'id' => 1,
            'score' => 2,
            'miss_score' => 1,
        ];
        $result = $this->getProcessor()->processRule($question);

        $this->assertEquals(
            [
                ['name' => 'all_right', 'score' => 2],
                ['name' => 'part_right', 'score' => 1],
                ['name' => 'no_answer', 'score' => 0],
                ['name' => 'wrong', 'score' => 0],
            ],
            $result
        );
    }

    protected function getProcessor()
    {
        return $this->biz['score_rule_processor'];
    }
}
