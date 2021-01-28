<?php

namespace Codeages\Biz\ItemBank\Assessment\ScoreRule;

use Codeages\Biz\ItemBank\Answer\Service\AnswerQuestionReportService;

class PartRightScoreRule extends ScoreRule
{
    const RULE = 'part_right';

    public function review($questionResult, $score)
    {
        if ('wrong' != $questionResult['result']) {
            return ['status' => '', 'score' => 0];
        }

        if (in_array('wrong', $questionResult['response_points_result'])) {
            return ['status' => '', 'score' => 0];
        }

        if (!in_array('right', $questionResult['response_points_result'])) {
            return ['status' => '', 'score' => 0];
        }

        return ['status' => AnswerQuestionReportService::STATUS_PART_RIGHT, 'score' => $score];
    }

    public function processRule($question)
    {
        if (!empty($question['miss_score'])) {
            return [
                'name' => self::RULE,
                'score' => $question['miss_score'],
            ];
        }

        return [];
    }

    public function setQuestionScore($question, $score)
    {
        $question['miss_score'] = $score;

        return $question;
    }
}
