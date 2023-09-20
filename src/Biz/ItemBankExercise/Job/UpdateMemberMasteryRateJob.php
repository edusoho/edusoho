<?php

namespace Biz\ItemBankExercise\Job;

use AppBundle\Common\ArrayToolkit;
use Biz\ItemBankExercise\Service\AssessmentExerciseService;
use Biz\ItemBankExercise\Service\ChapterExerciseService;
use Biz\ItemBankExercise\Service\ExerciseMemberService;
use Biz\ItemBankExercise\Service\ExerciseModuleService;
use Biz\ItemBankExercise\Service\ExerciseQuestionRecordService;
use Biz\ItemBankExercise\Service\ExerciseService;
use Codeages\Biz\Framework\Scheduler\AbstractJob;
use Codeages\Biz\ItemBank\Assessment\Service\AssessmentSectionItemService;
use Codeages\Biz\ItemBank\Item\Service\ItemService;

class UpdateMemberMasteryRateJob extends AbstractJob
{
    private $questionNum = 0;

    private $exerciseId = 0;

    private $itemIds = [];

    public function execute()
    {
        $this->setParams();

        $this->updateData();
    }

    protected function setParams()
    {
        $itemBankExercise = $this->getItemBankExerciseService()->get($this->args['itemBankExerciseId']);
        $this->exerciseId = $itemBankExercise['id'];

        $items = [];
        if ($itemBankExercise['chapterEnable']) {
            $items = array_merge($items, $this->getChapterItems($itemBankExercise['questionBankId']));
        }
        if ($itemBankExercise['assessmentEnable']) {
            $items = array_merge($items, $this->getAssessmentItems());
        }

        $items = ArrayToolkit::index($items, 'id');
        $this->itemIds = array_column($items, 'id');
        $this->questionNum = array_sum(array_column($items, 'question_num'));
    }

    protected function updateData()
    {
        if (0 == $this->questionNum) {
            $this->biz['db']->executeUpdate('UPDATE item_bank_exercise_member SET doneQuestionNum = 0, rightQuestionNum = 0, masteryRate = 0, completionRate = 0 WHERE exerciseId = ?;', [$this->exerciseId]);

            return;
        }

        $rightNumWrongNums = $this->getItemBankExerciseQuestionRecordService()->countQuestionRecordStatus($this->exerciseId, $this->itemIds);
        $rightNumWrongNumGroups = ArrayToolkit::group($rightNumWrongNums, 'userId');

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
            $updateMembers[$member['id']] = [
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

    private function getChapterItems($questionBankId)
    {
        $chapters = $this->getItemBankChapterExerciseService()->getPublishChapterTreeList($questionBankId);
        if (empty($chapters)) {
            return [];
        }

        return $this->getItemService()->findItemsByCategoryIds(array_column($chapters, 'id'));
    }

    private function getAssessmentItems()
    {
        $modules = $this->getItemBankExerciseModuleService()->findByExerciseIdAndType($this->exerciseId, ExerciseModuleService::TYPE_ASSESSMENT);
        if (empty($modules)) {
            return [];
        }
        $assessmentExercises = $this->getItemBankAssessmentExerciseService()->findByModuleIds(array_column($modules, 'id'));
        if (empty($assessmentExercises)) {
            return [];
        }
        $assessmentItems = $this->getSectionItemService()->findSectionItemsByAssessmentIds(array_column($assessmentExercises, 'assessmentId'));
        if (empty($assessmentItems)) {
            return [];
        }

        return $this->getItemService()->findItemsByIds(array_column($assessmentItems, 'item_id'));
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

    /**
     * @return AssessmentExerciseService
     */
    protected function getItemBankAssessmentExerciseService()
    {
        return $this->biz->service('ItemBankExercise:AssessmentExerciseService');
    }

    /**
     * @return ExerciseModuleService
     */
    protected function getItemBankExerciseModuleService()
    {
        return $this->biz->service('ItemBankExercise:ExerciseModuleService');
    }

    /**
     * @return AssessmentSectionItemService
     */
    protected function getSectionItemService()
    {
        return $this->biz->service('ItemBank:Assessment:AssessmentSectionItemService');
    }

    /**
     * @return ChapterExerciseService
     */
    protected function getItemBankChapterExerciseService()
    {
        return $this->biz->service('ItemBankExercise:ChapterExerciseService');
    }
}
