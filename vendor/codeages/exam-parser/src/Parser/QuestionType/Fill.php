<?php

namespace ExamParser\Parser\QuestionType;

use ExamParser\Constants\QuestionElement;
use ExamParser\Constants\QuestionErrors;

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

            //处理题干
            if ($this->matchStem($question, $line, $preNode)) {
                continue;
            }
        }
        $question['stemShow'] = preg_replace('/\[\[(\S|\s)*?\]\]/', '___', $question['stem']);
        $this->checkErrors($question);

        return $question;
    }

    protected function matchAnswers(&$question, $line, &$preNode)
    {
        $pattern = '/\[\[(\S|\s)*?\]\]/';

        if (preg_match_all($pattern, $line, $matches)) {
            foreach ($matches[0] as &$answer) {
                $answer = ltrim($answer, '[');
                $answer = rtrim($answer, ']');
            }
            $question['answers'] = $matches[0];
            $question['stem'] .= preg_replace('/^((\d{0,5}(\.|、|。|\s))|((\(|（)\d{0,5}(\)|）)))/', '', $line);
            $preNode = QuestionElement::ANSWERS;

            return true;
        }

        return false;
    }

    protected function checkErrors(&$question)
    {
        //判断题干是否有错
        if (empty($question[QuestionElement::STEM])) {
            $question['errors'][QuestionElement::STEM] = $this->getError(QuestionElement::STEM, QuestionErrors::NO_STEM);
        }

        //判断答案是否有错
        foreach ($question[QuestionElement::ANSWERS] as $key => $answer) {
            if (trim($answer) == '') {
                $question['errors'][QuestionElement::ANSWERS.'_'.$key] = $this->getError(QuestionElement::ANSWERS, QuestionErrors::NO_ANSWER, $key);
            }
        }
    }
}
