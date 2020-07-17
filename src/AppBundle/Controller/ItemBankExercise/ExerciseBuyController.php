<?php

namespace AppBundle\Controller\ItemBankExercise;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use AppBundle\Controller\BuyFlowController;
use Biz\Course\Service\MemberService;
use Biz\ItemBankExercise\ItemBankExerciseException;
use Biz\ItemBankExercise\Service\AssessmentExerciseService;
use Biz\ItemBankExercise\Service\ExerciseMemberService;
use Biz\ItemBankExercise\Service\ExerciseModuleService;
use Biz\ItemBankExercise\Service\ExerciseService;
use Biz\QuestionBank\Service\QuestionBankService;
use Biz\Testpaper\TestpaperException;
use Codeages\Biz\ItemBank\Assessment\Service\AssessmentService;
use Codeages\Biz\ItemBank\Item\Service\ItemCategoryService;
use Symfony\Component\HttpFoundation\Request;

class ExerciseBuyController extends BuyFlowController
{
    protected $targetType = 'item_bank_exercise';

    protected function getSuccessUrl($id)
    {
        return $this->generateUrl('my_item_bank_exercise_show', array('id' => $id));
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