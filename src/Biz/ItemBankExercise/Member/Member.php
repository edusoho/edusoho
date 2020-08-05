<?php

namespace Biz\ItemBankExercise\Member;

use Biz\ItemBankExercise\Dao\ExerciseMemberDao;
use Biz\ItemBankExercise\Service\ExerciseMemberService;
use Biz\ItemBankExercise\Service\ExerciseService;
use Codeages\Biz\Framework\Context\Biz;

abstract class Member
{
    protected $biz;

    public function __construct(Biz $biz)
    {
        $this->biz = $biz;
    }

    public function join($exerciseId, $userId, $info = [])
    {
        try {
            $this->biz['db']->beginTransaction();

            $exercise = $this->beforeAdd($exerciseId, $userId, $info);
            $member = $this->addMember($exercise, $userId, $info);
            $this->afterAdd($member, $exercise, $info);

            $this->biz['db']->commit();

            return $member;
        } catch (\Exception $e) {
            $this->biz['db']->rollback();
            throw $e;
        }
    }

    abstract protected function addMember($exercise, $userId, $info);

    abstract protected function beforeAdd($exerciseId, $userId, $info);

    abstract protected function afterAdd($member, $exercise, $info);

    /**
     * @return ExerciseService
     */
    protected function getExerciseService()
    {
        return $this->biz->service('ItemBankExercise:ExerciseService');
    }

    /**
     * @return ExerciseMemberService
     */
    protected function getExerciseMemberService()
    {
        return $this->biz->service('ItemBankExercise:ExerciseMemberService');
    }

    /**
     * @return ExerciseMemberDao
     */
    protected function getMemberDao()
    {
        return $this->biz->dao('ItemBankExercise:ExerciseMemberDao');
    }
}
