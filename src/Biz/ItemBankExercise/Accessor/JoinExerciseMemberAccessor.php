<?php

namespace Biz\ItemBankExercise\Accessor;

use Biz\Accessor\AccessorAdapter;

class JoinExerciseMemberAccessor extends AccessorAdapter
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

        if ($this->getItemBankExerciseMemberService()->isExerciseMember($itemBankExercise['id'], $user->getId())) {
            return $this->buildResult('member.member_exist', ['userId' => $user['id']]);
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
