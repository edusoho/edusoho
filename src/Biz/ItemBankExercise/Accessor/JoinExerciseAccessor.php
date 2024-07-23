<?php

namespace Biz\ItemBankExercise\Accessor;

use Biz\Accessor\AccessorAdapter;
use Biz\ItemBankExercise\ExpiryMode\ExpiryModeFactory;

class JoinExerciseAccessor extends AccessorAdapter
{
    public function access($itemBankExercise)
    {
        if (empty($itemBankExercise)) {
            return $this->buildResult('item_bank_exercise.not_found');
        }

        if ('draft' === $itemBankExercise['status']) {
            return $this->buildResult('item_bank_exercise.unpublished', ['exerciseId' => $itemBankExercise['id']]);
        }

        if ('closed' === $itemBankExercise['status']) {
            return $this->buildResult('item_bank_exercise.closed', ['exerciseId' => $itemBankExercise['id']]);
        }

        if (!$itemBankExercise['joinEnable']) {
            return $this->buildResult('item_bank_exercise.not_join_enable', ['exerciseId' => $itemBankExercise['id']]);
        }

        if ($this->isNotArriving($itemBankExercise)) {
            return $this->buildResult('item_bank_exercise.not_arrive', ['exerciseId' => $itemBankExercise['id']]);
        }

        if (ExpiryModeFactory::create($itemBankExercise['expiryMode'])->isExpired($itemBankExercise)) {
            return $this->buildResult('item_bank_exercise.expired', ['exerciseId' => $itemBankExercise['id']]);
        }

        return null;
    }

    private function isNotArriving ($itemBankExercise)
    {
        return 'date' == $itemBankExercise['expiryMode'] && $itemBankExercise['expiryStartDate'] > time();
    }
}
