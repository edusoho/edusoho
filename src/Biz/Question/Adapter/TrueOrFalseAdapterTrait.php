<?php

namespace Biz\Question\Adapter;

use Codeages\Biz\ItemBank\Item\Type\DetermineItem;

trait TrueOrFalseAdapterTrait
{
    private function adaptTrueOrFalse($question)
    {
        $adaptQuestion = $this->adaptQuestion($question);
        $adaptQuestion['type'] = DetermineItem::TYPE;
        $adaptQuestion['answer'] = $this->adaptTrueOrFalseAnswer($question);

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
}
