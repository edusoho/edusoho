<?php

namespace Biz\Question\Adapter;

use Codeages\Biz\ItemBank\Item\Type\EssayItem;
use ExamParser\Constants\QuestionElement;
use ExamParser\Constants\QuestionErrors;

trait EssayAdapterTrait
{
    private function adaptEssay($question)
    {
        $adaptQuestion = $this->adaptQuestion($question);
        $adaptQuestion['type'] = EssayItem::TYPE;
        $adaptQuestion['answer'] = $question['answer']['correct'][0] ?? '';
        $errors = $this->adaptEssayErrors($question);
        if ($errors) {
            $adaptQuestion['errors'] = empty($adaptQuestion['errors']) ? $errors : array_merge($adaptQuestion['errors'], $errors);
        }

        return $adaptQuestion;
    }

    private function adaptEssayErrors($question)
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
