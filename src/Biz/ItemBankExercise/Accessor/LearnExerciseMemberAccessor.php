<?php

namespace Biz\ItemBankExercise\Accessor;

use Biz\Accessor\AccessorAdapter;

class LearnExerciseMemberAccessor extends AccessorAdapter
{
    public function access($itemBankExercise)
    {
        $user = $this->getCurrentUser();
        if (null === $user || !$user->isLogin()) {
            return $this->buildResult('user.not_login');
        }

        if ($user['locked']) {
            return $this->buildResult('user.locked', ['userId' => $user['id']]);
        }

        $member = $this->getItemBankExerciseMemberService()->getExerciseStudent($itemBankExercise['id'], $user['id']);
        if (empty($member)) {
            return $this->buildResult('member.not_found');
        }

        if ($member['deadline'] > 0 && $member['deadline'] < time()) {
            return $this->buildResult('member.expired', ['userId' => $user['id']]);
        }
        if (0 == $member['canLearn']) {
            return $this->buildResult('item_bank_exercise.closed');
        }

        return null;
    }

    /**
     * @return \Biz\ItemBankExercise\Service\ExerciseMemberService
     */
    protected function getItemBankExerciseMemberService()
    {
        return $this->biz->service('ItemBankExercise:ExerciseMemberService');
    }
}
