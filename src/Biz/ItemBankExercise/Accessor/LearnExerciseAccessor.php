<?php

namespace Biz\ItemBankExercise\Accessor;

use Biz\Accessor\AccessorAdapter;

class LearnExerciseAccessor extends AccessorAdapter
{
    public function access($itemBankExercise)
    {
        if (empty($itemBankExercise)) {
            return $this->buildResult('item_bank_exercise.not_found');
        }

        if ('draft' === $itemBankExercise['status']) {
            return $this->buildResult('item_bank_exercise.unpublished', ['exerciseId' => $itemBankExercise['id']]);
        }

        if ($this->isNotArriving($itemBankExercise)) {
            return $this->buildResult('item_bank_exercise.not_arrive', ['exerciseId' => $itemBankExercise['id']]);
        }

        return null;
    }

    private function isNotArriving($itemBankExercise)
    {
        return 'date' == $itemBankExercise['expiryMode'] && $itemBankExercise['expiryStartDate'] > time();
    }
}
