<?php

namespace AppBundle\Controller\ItemBankExercise;

use AppBundle\Controller\BuyFlowController;
use Biz\ItemBankExercise\Service\ExerciseMemberService;
use Biz\ItemBankExercise\Service\ExerciseService;

class ExerciseBuyController extends BuyFlowController
{
    protected $targetType = 'item_bank_exercise';

    protected function getSuccessUrl($id)
    {
        return $this->generateUrl('my_item_bank_exercise_show', ['id' => $id]);
    }

    protected function isJoined($id)
    {
        $user = $this->getUser();
        $member = $this->getExerciseMemberService()->getExerciseMember($id, $user['id']);
        if (!empty($member)) {
            $this->getExerciseService()->get($id);
        }

        return $member;
    }

    protected function tryFreeJoin($id)
    {
        $this->getExerciseService()->freeJoinExercise($id);
    }

    /**
     * @return ExerciseService
     */
    private function getExerciseService()
    {
        return $this->createService('ItemBankExercise:ExerciseService');
    }

    /**
     * @return ExerciseMemberService
     */
    private function getExerciseMemberService()
    {
        return $this->createService('ItemBankExercise:ExerciseMemberService');
    }
}
