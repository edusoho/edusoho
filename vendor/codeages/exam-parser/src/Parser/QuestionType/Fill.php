<?php

namespace ExamParser\Parser\QuestionType;

use ExamParser\Constants\QuestionElement;

class Fill extends AbstractQuestion
{
    public function convert($questionLines)
    {
        $question = array(
            'type' => 'fill',
            'stem' => '',
            'difficulty' => 'normal',
            'score' => 2.0,
            'analysis' => '',
            'answers' => array(),
        );
        $answers = array();
        $preNode = QuestionElement::STEM;
        foreach ($questionLines as $line) {
            //处理答案
            if ($this->matchAnswers($question, $line, $preNode)) {
                continue;
            }
            //处理难度
            if ($this->matchDifficulty($question, $line, $preNode)) {
                continue;
            }
            //处理分数
            if ($this->matchScore($question, $line, $preNode)) {
                continue;
            }

            //处理解析
            if ($this->matchAnalysis($question, $line, $preNode)) {
                continue;
            }

            if (QuestionElement::STEM == $preNode) {
                $question['stem'] .= preg_replace('/^\d{0,5}(\.|、|。|\s)/', '', $line).PHP_EOL;
            }
        }

        return $question;
    }

    protected function matchAnswers(&$question, $line, &$preNode)
    {
        $pattern = '/\[\[(\S|\s).*?\]\]/';

        if (preg_match_all($pattern, $line, $matches)) {
            foreach ($matches[0] as &$answer) {
                $answer = ltrim($answer, '[');
                $answer = rtrim($answer, ']');
            }
            $question['answers'] = $matches[0];
            $question['stem'] .= preg_replace('/^\d{0,5}(\.|、|。|\s)/', '', $line).PHP_EOL;
            $preNode = QuestionElement::ANSWERS;

            return true;
        }

        return false;
    }
}
