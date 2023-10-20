<?php

namespace Biz\Question\Adapter;

use Codeages\Biz\ItemBank\Item\Type\MaterialItem;

trait MaterialAdapterTrait
{
    private function adaptMaterial($question)
    {
        $adaptQuestion = $this->adaptQuestion($question);
        $adaptQuestion['type'] = MaterialItem::TYPE;
        $adaptQuestion['subQuestions'] = empty($question['children']) ? [] : $this->adaptQuestions($question['children']);
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
