<?php

namespace Codeages\Biz\ItemBank\Assessment\ScoreRule;

use Codeages\Biz\Framework\Service\Exception\NotFoundException;
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
        if (in_array($question['answer_mode'], ['choice', 'uncertain_choice', 'text'])) {
            return [
                'name' => self::RULE,
                'score' => empty($question['miss_score']) ? 0 : $question['miss_score'],
                'rule' => empty($question['score_rule']) ?[]:$question['score_rule'],
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
