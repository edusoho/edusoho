<?php

namespace Biz\Question\Adapter;

use Codeages\Biz\ItemBank\Item\Type\ChoiceItem;
use Codeages\Biz\ItemBank\Item\Type\SingleChoiceItem;
use Codeages\Biz\ItemBank\Item\Type\UncertainChoiceItem;
use ExamParser\Constants\QuestionElement;
use ExamParser\Constants\QuestionErrors;

trait ChoiceAdapterTrait
{
    private function adaptChoice($question)
    {
        $adaptQuestion = $this->adaptQuestion($question);
        $adaptQuestion['type'] = SingleChoiceItem::TYPE;
        $adaptQuestion['options'] = $this->adaptOptions($question);
        $adaptQuestion['answers'] = $this->adaptChoiceAnswer($question);
        $errors = $this->adaptChoiceErrors($question);
        if ($errors) {
            $adaptQuestion['errors'] = empty($adaptQuestion['errors']) ? $errors : array_merge($adaptQuestion['errors'], $errors);
        }

        return $adaptQuestion;
    }

    private function adaptMultipleChoice($question)
    {
        $question = $this->adaptChoice($question);
        $question['type'] = ChoiceItem::TYPE;

        return $question;
    }

    private function adaptUncertainChoice($question)
    {
        $question = $this->adaptChoice($question);
        $question['type'] = UncertainChoiceItem::TYPE;

        return $question;
    }

    private function adaptOptions($question)
    {
        if (empty($question['body']['options'])) {
            return [];
        }

        return array_column($question['body']['options'], 'content');
    }

    private function adaptChoiceAnswer($question)
    {
        if (empty($question['answer']['correct'])) {
            return [];
        }

        return array_map(function ($answer) {
            return $answer - 1;
        }, $question['answer']['correct']);
    }

    private function adaptChoiceErrors($question)
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
