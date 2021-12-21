<?php

namespace Codeages\Biz\ItemBank\Assessment\ScoreRule;

use Codeages\Biz\Framework\Service\Exception\NotFoundException;
use Codeages\Biz\ItemBank\Answer\Service\AnswerQuestionReportService;

class PartRightScoreRule extends ScoreRule
{
    const RULE = 'part_right';

    public function review($questionResult, $rule)
    {

        if ('wrong' != $questionResult['result']) {
            return ['status' => '', 'score' => 0];
        }

        if(!empty($rule['score_rule']) && $rule['score_rule']['scoreType'] == 'option'){
            $result = $this->processScore($questionResult, $rule);
            if(!empty($result)){
                return $result;
            }
        }

        if (in_array('wrong', $questionResult['response_points_result'])) {
            return ['status' => '', 'score' => 0];
        }

        if (!in_array('right', $questionResult['response_points_result'])) {
            return ['status' => '', 'score' => 0];
        }

        return ['status' => AnswerQuestionReportService::STATUS_PART_RIGHT, 'score' => $rule['score']];
    }

    protected function processScore($questionResult, $rule)
    {
        if($questionResult['answer_mode'] == 'text'){
            $count = $this->countAnswers($questionResult);
            $otherScore = empty($rule['score_rule']['otherScore'])?0:$rule['score_rule']['otherScore'];
            return ['status' => AnswerQuestionReportService::STATUS_PART_RIGHT, 'score' => $count *$otherScore];
        }

        if(in_array($questionResult['answer_mode'], ['uncertain_choice', 'choice'])){
            if (in_array('wrong', $questionResult['response_points_result'])) {
                return ['status' => '', 'score' => 0];
            }
            $count = $this->countAnswers($questionResult);
            $otherScore = empty($rule['score_rule']['otherScore']) ? 0:$rule['score_rule']['otherScore'];
            return ['status' => AnswerQuestionReportService::STATUS_PART_RIGHT, 'score' => $count *$otherScore];
        }
        return  [];
    }

    private function countAnswers($questionResult){
        $count = 0;
        foreach ($questionResult['response_points_result'] as $result){
            if($result == 'right'){
                $count ++;
            }
        }
        return $count;
    }

    public function processRule($question)
    {
        if (in_array($question['answer_mode'], ['choice', 'uncertain_choice', 'text'])) {
            return [
                'name' => self::RULE,
                'score' => empty($question['miss_score']) ? 0 : $question['miss_score'],
                'score_rule' => empty($question['score_rule']) ?[]:$question['score_rule'],
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
