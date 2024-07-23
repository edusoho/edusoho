<?php

namespace Biz\Question\Adapter;

use Codeages\Biz\ItemBank\Item\Type\MaterialItem;
use ExamParser\Constants\QuestionElement;
use ExamParser\Constants\QuestionErrors;

trait MaterialAdapterTrait
{
    private function adaptMaterial($question)
    {
        $adaptQuestion = $this->adaptQuestion($question);
        $adaptQuestion['type'] = MaterialItem::TYPE;
        $adaptQuestion['subQuestions'] = empty($question['children']) ? [] : $this->adaptQuestions($question['children']);
        if (empty($adaptQuestion['subQuestions'])) {
            $adaptQuestion['errors'][QuestionElement::SUB_QUESTIONS] = $this->adaptError(QuestionElement::SUB_QUESTIONS, QuestionErrors::NO_SUB_QUESTIONS);
        }
        $errors = $this->adaptMaterialErrors($adaptQuestion['subQuestions']);
        if ($errors) {
            $adaptQuestion['errors'] = empty($adaptQuestion['errors']) ? $errors : array_merge($adaptQuestion['errors'], $errors);
        }

        return $adaptQuestion;
    }

    private function adaptMaterialErrors($subQuestions)
    {
        $errors = [];
        foreach ($subQuestions as $subQuestion) {
            if (!empty($subQuestion['errors'])) {
                $errors['hasSubError'] = true;
                break;
            }
        }

        return $errors;
    }
}
