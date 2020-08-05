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

        $member = $this->getItemBankExerciseMemberService()->getExerciseMember($itemBankExercise['id'], $user['id']);
        if (empty($member)) {
            return $this->buildResult('member.not_found');
        }

        if ($member['deadline'] > 0 && $member['deadline'] < time()) {
            return $this->buildResult('member.expired', ['userId' => $user['id']]);
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
