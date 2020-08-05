<?php

namespace Biz\ItemBankExercise\Job;

use AppBundle\Common\ArrayToolkit;
use Codeages\Biz\Framework\Scheduler\AbstractJob;

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
            $this->biz['db']->executeUpdate('UPDATE item_bank_exercise_member SET doneQuestionNum = 0, rightQuestionNum = 0, masteryRate = 0, completionRate = 0 WHERE exerciseId = ?;', [$this->exerciseId]);

            return;
        }

        $sql = 'SELECT userId, `status`, count(*) AS num from item_bank_exercise_question_record WHERE exerciseId = ? GROUP BY userId, `status`;';
        $rightNumWrongNumGroups = ArrayToolkit::group(
            $this->biz['db']->fetchAll($sql, [$this->exerciseId]),
            'userId'
        );

        $updateMembers = [];
        $members = $this->biz['db']->fetchAll('SELECT id, userId from item_bank_exercise_member WHERE exerciseId = ?;', [$this->exerciseId]);
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
            $this->getItemBankExerciseMemberDao()->batchUpdate(ArrayToolkit::column($updateMembers, 'id'), $updateMembers);
        }
    }

    /**
     * @return \Biz\QuestionBank\Service\QuestionBankService
     */
    protected function getQuestionBankService()
    {
        return $this->biz->service('QuestionBank:QuestionBankService');
    }

    /**
     * @return \Biz\ItemBankExercise\Service\ExerciseService
     */
    protected function getItemBankExerciseService()
    {
        return $this->biz->service('ItemBankExercise:ExerciseService');
    }

    /**
     * @return \Biz\ItemBankExercise\Service\ExerciseModuleService
     */
    protected function getItemBankExerciseModuleService()
    {
        return $this->biz->service('ItemBankExercise:ExerciseModuleService');
    }

    protected function getItemBankExerciseMemberDao()
    {
        return $this->biz->dao('ItemBankExercise:ExerciseMemberDao');
    }
}
