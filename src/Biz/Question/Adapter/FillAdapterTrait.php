<?php

namespace Biz\Question\Adapter;

use Codeages\Biz\ItemBank\Item\Type\FillItem;
use ExamParser\Constants\QuestionElement;
use ExamParser\Constants\QuestionErrors;

trait FillAdapterTrait
{
    private function adaptFill($question)
    {
        $adaptQuestion = $this->adaptQuestion($question);
        $adaptQuestion['type'] = FillItem::TYPE;
        $adaptQuestion['answers'] = $this->adaptFillAnswer($question);
        $adaptQuestion['stemShow'] = $adaptQuestion['stem'];
        $adaptQuestion['stem'] = $this->adaptFillStem($adaptQuestion);
        $errors = $this->adaptFillErrors($adaptQuestion);
        if ($errors) {
            $adaptQuestion['errors'] = empty($adaptQuestion['errors']) ? $errors : array_merge($adaptQuestion['errors'], $errors);
        }

        return $adaptQuestion;
    }

    private function adaptFillStem($question)
    {
        $key = 0;

        return preg_replace_callback('/_+?([^_])/', function ($match) use (&$key, $question) {
            $answer = empty($question['answers'][$key]) ? '' : $question['answers'][$key];
            ++$key;

            return "[[$answer]]$match[1]";
        }, $question['stem']);
    }

    private function adaptFillAnswer($question)
    {
        if (empty($question['answer']['correct'])) {
            return [];
        }

        return array_map(function ($answers) {
            return implode('|', array_column($answers, 'content'));
        }, $question['answer']['correct']);
    }

    private function adaptFillErrors($question)
    {
        $errors = [];
        foreach ($question[QuestionElement::ANSWERS] as $key => $answer) {
            if ('' == trim($answer)) {
                $errors[QuestionElement::ANSWERS.'_'.$key] = $this->adaptError(QuestionElement::ANSWERS, QuestionErrors::NO_ANSWER, $key);
            }
        }
        if (substr_count($question['stem'], '[[') < count($question['answers'])) {
            $errors[QuestionElement::STEM] = $this->adaptError(QuestionElement::STEM, QuestionErrors::LACK_ANSWER_POINT);
        }

        return $errors;
    }
}
