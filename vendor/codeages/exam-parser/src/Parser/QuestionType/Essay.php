<?php

namespace ExamParser\Parser\QuestionType;

use ExamParser\Constants\QuestionElement;
use ExamParser\Constants\QuestionErrors;

class Essay extends AbstractQuestion
{
    public function convert($questionLines)
    {
        $question = array(
            'type' => 'essay',
            'stem' => '',
            'difficulty' => 'normal',
            'score' => 2.0,
            'analysis' => '',
            'answer' => '',
        );
        $answers = array();
        $preNode = QuestionElement::STEM;
        foreach ($questionLines as $line) {
            //处理答案
            if ($this->matchAnswer($question, $line, $preNode)) {
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

        $this->checkErrors($question);

        return $question;
    }

    protected function matchAnswer(&$question, $line, &$preNode)
    {
        if (!$this->hasSignal($line) && $preNode == QuestionElement::ANSWER) {
            $question['answer'] .= '<br/>'.$line;
            return true;
        }
        
        if (0 === strpos(trim($line), self::ANSWER_SIGNAL)) {
            $answer = str_replace(self::ANSWER_SIGNAL, '', $line);
            $question['answer'] = $answer;
            $preNode = QuestionElement::ANSWER;

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
        if (trim($question[QuestionElement::ANSWER]) == '') {
            $question['errors'][QuestionElement::ANSWER] = $this->getError(QuestionElement::ANSWER, QuestionErrors::NO_ANSWER);
        }
    }
}
