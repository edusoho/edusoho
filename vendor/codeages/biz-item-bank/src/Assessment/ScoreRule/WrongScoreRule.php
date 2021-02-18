<?php

namespace Codeages\Biz\ItemBank\Assessment\ScoreRule;

use Codeages\Biz\ItemBank\Answer\Service\AnswerQuestionReportService;

class WrongScoreRule extends ScoreRule
{
    const RULE = 'wrong';

    public function review($questionResult, $score)
    {
        if ('wrong' == $questionResult['result'] && in_array('wrong', $questionResult['response_points_result'])) {
            return ['status' => AnswerQuestionReportService::STATUS_WRONG, 'score' => $score];
        }

        return ['status' => '', 'score' => 0];
    }

    public function processRule($question)
    {
        return [
            'name' => self::RULE,
            'score' => 0,
        ];
    }
}
