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
        if ('choice' == $question['type'] && !empty($adaptQuestion['answers'])) {
            $adaptQuestion['answers'] = [$adaptQuestion['answers'][0]];
        }
        $errors = $this->adaptChoiceErrors($adaptQuestion, $question);
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
            return ['', ''];
        }
        $options = array_column($question['body']['options'], null, 'id');
        ksort($options);

        $options = array_column($options, 'content');
        if (1 == count($options)) {
            $options[] = '';
        }

        return array_splice($options, 0, 10);
    }

    private function adaptChoiceAnswer($question)
    {
        if (empty($question['answer']['correct'])) {
            return [];
        }
        $answers = array_map(function ($answer) {
            return $answer - 1;
        }, $question['answer']['correct']);
        $options = $this->adaptOptions($question);

        return array_filter($answers, function ($answer) use ($options) {
            return isset($options[$answer]);
        });
    }

    private function adaptChoiceErrors($adaptQuestion, $question)
    {
        $errors = [];
        foreach ($adaptQuestion['options'] as $index => $option) {
            if (empty($option)) {
                $errors[QuestionElement::OPTIONS.'_'.$index] = $this->adaptError(QuestionElement::OPTIONS, QuestionErrors::NO_OPTION, $index);
            }
        }
        if (empty($adaptQuestion['answers'])) {
            $errors[QuestionElement::ANSWERS] = $this->adaptError(QuestionElement::ANSWERS, QuestionErrors::NO_ANSWER);
        }
        if ('multipleChoice' == $question['type'] && count(array_unique($adaptQuestion['answers'])) < 2) {
            $errors[QuestionElement::ANSWERS] = $this->adaptError(QuestionElement::ANSWERS, QuestionErrors::LACK_ANSWER);
        }
        if (empty($question['errors'])) {
            return $errors;
        }
        foreach ($question['errors'] as $error) {
            if ('OPTION_TEXT_EMPTY' == $error['code']) {
                $index = $error['option'] - 1;
                $errors[QuestionElement::OPTIONS.'_'.$index] = $this->adaptError(QuestionElement::OPTIONS, QuestionErrors::NO_OPTION, $index);
            }
        }

        return $errors;
    }
}
