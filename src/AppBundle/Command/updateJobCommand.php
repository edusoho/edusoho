<?php

namespace AppBundle\Command;

use AppBundle\Common\ArrayToolkit;
use Biz\ItemBankExercise\Service\AssessmentExerciseService;
use Biz\ItemBankExercise\Service\ChapterExerciseService;
use Biz\ItemBankExercise\Service\ExerciseMemberService;
use Biz\ItemBankExercise\Service\ExerciseModuleService;
use Biz\ItemBankExercise\Service\ExerciseQuestionRecordService;
use Biz\ItemBankExercise\Service\ExerciseService;
use Codeages\Biz\ItemBank\Assessment\Service\AssessmentSectionItemService;
use Codeages\Biz\ItemBank\Item\Service\ItemService;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class updateJobCommand extends BaseCommand
{
    protected function configure()
    {
        $this->setName('util:update-job')
            ->addArgument('exerciseId', InputArgument::REQUIRED);
    }

    public $questionNum = 0;

    public $exerciseId = 0;

    public $itemIds = [];

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->setParams($input->getArgument('exerciseId'), $output);

        $this->updateData($input, $output);
    }

    protected function setParams($exerciseId, OutputInterface $output)
    {
        $itemBankExercise = $this->getItemBankExerciseService()->get($exerciseId);
        $this->exerciseId = $itemBankExercise['id'];

        list($chapterItemIds, $chapterQuestionIds) = $this->getChapterItemIdsAndQuestionIds($itemBankExercise['questionBankId'], $output);
        list($assessmentItemIds, $assessmentQuestionIds) = $this->getAssessmentItemIdsAndQuestionIds($output);

        $this->itemIds = array_unique(array_merge($chapterItemIds, $assessmentItemIds));
        $this->questionNum = count(array_unique(array_merge($chapterQuestionIds, $assessmentQuestionIds)));
    }

    protected function updateData(InputInterface $input, OutputInterface $output)
    {
        if (0 == $this->questionNum) {
            $this->getBiz()['db']->executeUpdate('UPDATE item_bank_exercise_member SET doneQuestionNum = 0, rightQuestionNum = 0, masteryRate = 0, completionRate = 0 WHERE exerciseId = ?;', [$this->exerciseId]);

            return;
        }

        $chapterRightNumWrongNums = $this->getItemBankExerciseQuestionRecordService()->countQuestionRecordStatusByModuleType($this->exerciseId, $this->itemIds, 'chapter');
        $assessmentRightNumWrongNums = $this->getItemBankExerciseQuestionRecordService()->countQuestionRecordStatusByModuleType($this->exerciseId, $this->itemIds, 'assessment');
        $rightNumWrongNums = array_merge($chapterRightNumWrongNums, $assessmentRightNumWrongNums);
        if (empty($rightNumWrongNums)) {
            return;
        }
        $rightNumWrongNumGroups = ArrayToolkit::group($rightNumWrongNums, 'userId');

        $updateMembers = [];
        $members = $this->getBiz()['db']->fetchAll('SELECT id, userId from item_bank_exercise_member WHERE exerciseId = ?;', [$this->exerciseId]);
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

    protected function getChapterItemIdsAndQuestionIds($questionBankId, OutputInterface $output)
    {
        $chapters = $this->getItemBankChapterExerciseService()->getChapterTreeList($questionBankId);
        if (empty($chapters)) {
            return [[], []];
        }

        $chapterItems = $this->getItemService()->findItemsByCategoryIds(ArrayToolkit::column($chapters, 'id'));
        if (empty($chapterItems)) {
            return [[], []];
        }
        $chapterItemIds = ArrayToolkit::column($chapterItems, 'id');
        $chapterQuestions = $this->getItemService()->findQuestionsByItemIds($chapterItemIds);
        if (empty($chapterQuestions)) {
            return [[], []];
        }
        $chapterQuestionIds = ArrayToolkit::column($chapterQuestions, 'id');

        return [$chapterItemIds, $chapterQuestionIds];
    }

    protected function getAssessmentItemIdsAndQuestionIds(OutputInterface $output)
    {
        $module = $this->getItemBankExerciseModuleService()->findByExerciseIdAndType($this->exerciseId, ExerciseModuleService::TYPE_ASSESSMENT);
        if (empty($module)) {
            return [[], []];
        }
        $assessmentExercises = $this->getItemBankAssessmentExerciseService()->findByModuleIds(ArrayToolkit::column($module, 'id'));
        if (empty($assessmentExercises)) {
            return [[], []];
        }
        $assessmentItems = $this->getSectionItemService()->findSectionItemsByAssessmentIds(ArrayToolkit::column($assessmentExercises, 'assessmentId'));
        if (empty($assessmentItems)) {
            return [[], []];
        }
        $assessmentItemIds = ArrayToolkit::column($assessmentItems, 'item_id');
        $assessmentQuestions = $this->getItemService()->findQuestionsByItemIds($assessmentItemIds);
        if (empty($assessmentQuestions)) {
            return [[], []];
        }
        $assessmentQuestionIds = ArrayToolkit::column($assessmentQuestions, 'id');

        return [$assessmentItemIds, $assessmentQuestionIds];
    }

    /**
     * @return ExerciseService
     */
    protected function getItemBankExerciseService()
    {
        return $this->createService('ItemBankExercise:ExerciseService');
    }

    /**
     * @return ItemService
     */
    protected function getItemService()
    {
        return $this->createService('ItemBank:Item:ItemService');
    }

    /**
     * @return ExerciseQuestionRecordService
     */
    protected function getItemBankExerciseQuestionRecordService()
    {
        return $this->createService('ItemBankExercise:ExerciseQuestionRecordService');
    }

    /**
     * @return ExerciseMemberService
     */
    protected function getExerciseMemberService()
    {
        return $this->createService('ItemBankExercise:ExerciseMemberService');
    }

    /**
     * @return AssessmentExerciseService
     */
    protected function getItemBankAssessmentExerciseService()
    {
        return $this->createService('ItemBankExercise:AssessmentExerciseService');
    }

    /**
     * @return ExerciseModuleService
     */
    protected function getItemBankExerciseModuleService()
    {
        return $this->createService('ItemBankExercise:ExerciseModuleService');
    }

    /**
     * @return AssessmentSectionItemService
     */
    protected function getSectionItemService()
    {
        return $this->createService('ItemBank:Assessment:AssessmentSectionItemService');
    }

    /**
     * @return ChapterExerciseService
     */
    protected function getItemBankChapterExerciseService()
    {
        return $this->createService('ItemBankExercise:ChapterExerciseService');
    }
}
