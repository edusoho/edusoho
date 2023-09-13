<?php

namespace Biz\ItemBankExercise\Job;

use AppBundle\Common\ArrayToolkit;
use Biz\ItemBankExercise\Service\ExerciseMemberService;
use Biz\ItemBankExercise\Service\ExerciseQuestionRecordService;
use Biz\ItemBankExercise\Service\ExerciseService;
use Biz\QuestionBank\Service\QuestionBankService;
use Codeages\Biz\Framework\Scheduler\AbstractJob;
use Codeages\Biz\ItemBank\Item\Service\ItemService;

class UpdateMemberMasteryRateJob extends AbstractJob
{
    public $questionNum = 0;

    public $exerciseId = 0;

    public function execute()
    {
        $this->setParams();

        $this->updateData();
    }

    protected function setParams()
    {
        $itemBankExericse = $this->getItemBankExerciseService()->get($this->args['itemBankExericseId']);
        $this->exerciseId = $itemBankExericse['id'];

        $questionBank = $this->getQuestionBankService()->getQuestionBank($itemBankExericse['questionBankId']);
        $this->questionNum = $questionBank['itemBank']['question_num'];
    }

    protected function updateData()
    {
        if (0 == $this->questionNum) {
            $this->getExerciseMemberService()->update($this->exerciseId, [
                'doneQuestionNum' => 0,
                'rightQuestionNum' => 0,
                'masteryRate' => 0,
                'completionRate' => 0,
            ]);

            return;
        }

        $itemBankExercise = $this->getItemBankExerciseService()->get($this->exerciseId);
        $items = $this->getItemService()->findItemsByCategoryIds($itemBankExercise['hiddenChapterIds']);
        if ($items) {
            $questions = $this->getItemService()->findQuestionsByItemIds(ArrayToolkit::column($items, 'id'));
            $questionIds = ArrayToolkit::column($questions, 'id');
        }

        $rightNumWrongNums = $this->getItemBankExerciseQuestionRecordService()->countQuestionRecordStatus($this->exerciseId, ($questionIds ?? [-1]));
        if (empty($rightNumWrongNums)) {
            return;
        }
        $rightNumWrongNumGroups = ArrayToolkit::group($rightNumWrongNums, 'userId');

        $updateMembers = [];
        $members = $this->getExerciseMemberService()->findByExerciseId($this->exerciseId);
        foreach ($members as $member) {
            $doneQuestionNum = $rightQuestionNum = 0;
            if (!empty($rightNumWrongNumGroups[$member['userId']])) {
                foreach ($rightNumWrongNumGroups[$member['userId']] as $rightNumWrongNumGroup) {
                    $doneQuestionNum += $rightNumWrongNumGroup['num'];
                    'right' == $rightNumWrongNumGroup['status'] && $rightQuestionNum += $rightNumWrongNumGroup['num'];
                }
            }
            $updateMembers[] = [
                'id' => $member['id'],
                'doneQuestionNum' => $doneQuestionNum,
                'rightQuestionNum' => $rightQuestionNum,
                'masteryRate' => round($rightQuestionNum / $this->questionNum * 100, 1),
                'completionRate' => round($doneQuestionNum / $this->questionNum * 100, 1),
            ];
        }

        if (count($members)) {
            $this->getExerciseMemberService()->batchUpdateMembers($updateMembers);
        }
    }

    /**
     * @return QuestionBankService
     */
    protected function getQuestionBankService()
    {
        return $this->biz->service('QuestionBank:QuestionBankService');
    }

    /**
     * @return ExerciseService
     */
    protected function getItemBankExerciseService()
    {
        return $this->biz->service('ItemBankExercise:ExerciseService');
    }

    /**
     * @return ItemService
     */
    protected function getItemService()
    {
        return $this->biz->service('ItemBank:Item:ItemService');
    }

    /**
     * @return ExerciseQuestionRecordService
     */
    protected function getItemBankExerciseQuestionRecordService()
    {
        return $this->biz->service('ItemBankExercise:ExerciseQuestionRecordService');
    }

    /**
     * @return ExerciseMemberService
     */
    protected function getExerciseMemberService()
    {
        return $this->biz->service('ItemBankExercise:ExerciseMemberService');
    }
}
