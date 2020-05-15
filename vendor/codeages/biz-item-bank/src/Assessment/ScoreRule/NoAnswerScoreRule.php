<?php

namespace Codeages\Biz\ItemBank\Assessment\ScoreRule;

use Codeages\Biz\ItemBank\Answer\Service\AnswerQuestionReportService;

class NoAnswerScoreRule extends ScoreRule
{
    const RULE = 'no_answer';

    public function review($questionResult, $score)
    {
        if ('wrong' == $questionResult['result']) {
            foreach ($questionResult['response_points_result'] as $result) {
                if ('none' != $result) {
                    return ['status' => '', 'score' => 0];
                }
            }

            return ['status' => AnswerQuestionReportService::STATUS_NOANSWER, 'score' => $score];
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
