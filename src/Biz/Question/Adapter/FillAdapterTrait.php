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
        $errors = $this->adaptFillErrors($question);
        if ($errors) {
            $adaptQuestion['errors'] = empty($adaptQuestion['errors']) ? $errors : array_merge($adaptQuestion['errors'], $errors);
        }

        return $adaptQuestion;
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
        if (empty($question['errors'])) {
            return [];
        }
        $errors = [];
        foreach ($question['errors'] as $error) {
            if ('ANSWER_MISSING' == $error['code']) {
                $errors[QuestionElement::ANSWERS] = $this->adaptError(QuestionElement::ANSWERS, QuestionErrors::NO_ANSWER);
            }
        }

        return $errors;
    }
}
