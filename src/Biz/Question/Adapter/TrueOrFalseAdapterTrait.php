<?php

namespace Biz\Question\Adapter;

use Codeages\Biz\ItemBank\Item\Type\DetermineItem;
use ExamParser\Constants\QuestionElement;
use ExamParser\Constants\QuestionErrors;

trait TrueOrFalseAdapterTrait
{
    private function adaptTrueOrFalse($question)
    {
        $adaptQuestion = $this->adaptQuestion($question);
        $adaptQuestion['type'] = DetermineItem::TYPE;
        $adaptQuestion['answer'] = $this->adaptTrueOrFalseAnswer($question);
        $errors = $this->adaptTrueOrFalseErrors($question);
        if ($errors) {
            $adaptQuestion['errors'] = empty($adaptQuestion['errors']) ? $errors : array_merge($adaptQuestion['errors'], $errors);
        }

        return $adaptQuestion;
    }

    private function adaptTrueOrFalseAnswer($question)
    {
        if (empty($question['answer']['correct'][0])) {
            return null;
        }

        $answers = [
            '1' => true,
            '2' => false,
        ];

        return $answers[$question['answer']['correct'][0]];
    }

    private function adaptTrueOrFalseErrors($question)
    {
        if (empty($question['errors'])) {
            return [];
        }
        $errors = [];
        foreach ($question['errors'] as $error) {
            if ('ANSWER_MISSING' == $error['code']) {
                $errors[QuestionElement::ANSWER] = $this->adaptError(QuestionElement::ANSWER, QuestionErrors::NO_ANSWER);
            }
        }

        return $errors;
    }
}
