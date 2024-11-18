<?php

namespace Codeages\Biz\ItemBank\Answer\Service\Impl;

use Codeages\Biz\Framework\Util\ArrayToolkit;
use Codeages\Biz\ItemBank\Answer\Dao\AnswerRandomSeqRecordDao;
use Codeages\Biz\ItemBank\Answer\Service\AnswerRandomSeqService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerRecordService;
use Codeages\Biz\ItemBank\Assessment\Service\AssessmentSectionItemService;
use Codeages\Biz\ItemBank\Assessment\Service\AssessmentService;
use Codeages\Biz\ItemBank\BaseService;
use Codeages\Biz\ItemBank\Item\AnswerMode\ChoiceAnswerMode;
use Codeages\Biz\ItemBank\Item\AnswerMode\SingleChoiceAnswerMode;
use Codeages\Biz\ItemBank\Item\AnswerMode\UncertainChoiceAnswerMode;
use Codeages\Biz\ItemBank\Item\Service\ItemService;
use Codeages\Biz\ItemBank\Item\Type\ChoiceItem;
use Codeages\Biz\ItemBank\Item\Type\SingleChoiceItem;
use Codeages\Biz\ItemBank\Item\Type\UncertainChoiceItem;

class AnswerRandomSeqServiceImpl extends BaseService implements AnswerRandomSeqService
{
    public function createAnswerRandomSeqRecordIfNecessary($answerRecordId)
    {
        $answerRecord = $this->getAnswerRecordService()->get($answerRecordId);
        if (empty($answerRecord)) {
            return;
        }
        if (!$this->isRandomSeq($answerRecord)) {
            return;
        }
        $sectionItems = $this->getSectionItemService()->searchAssessmentSectionItems(['assessment_id' => $answerRecord['assessment_id']], [], 0, PHP_INT_MAX, ['item_id', 'section_id']);
        if (empty($sectionItems)) {
            return;
        }

        return $this->getAnswerRandomSeqRecordDao()->create([
            'answer_record_id' => $answerRecord['id'],
            'items_random_seq' => $answerRecord['is_items_seq_random'] ? $this->buildAssessmentItemsRandomSeq($sectionItems) : [],
            'options_random_seq' => $answerRecord['is_options_seq_random'] ? $this->buildAssessmentChoiceItemOptionsRandomSeq($sectionItems) : [],
        ]);
    }

    public function shuffleItemsAndOptionsIfNecessary($assessment, $answerRecordId)
    {
        $answerRecord = $this->getAnswerRecordService()->get($answerRecordId);
        if (!$this->isRandomSeq($answerRecord)) {
            return $assessment;
        }
        $answerRandomSeqRecord = $this->getAnswerRandomSeqRecordDao()->getByAnswerRecordId($answerRecordId);
        if ($answerRecord['is_items_seq_random']) {
            $assessment = $this->shuffleAssessmentItems($assessment, $answerRandomSeqRecord['items_random_seq']);
        }
        if ($answerRecord['is_options_seq_random']) {
            $assessment = $this->shuffleAssessmentChoiceItemOptions($assessment, $answerRandomSeqRecord['options_random_seq']);
        }

        return $assessment;
    }

    public function restoreOptionsToOriginalSeqIfNecessary($assessmentResponse)
    {
        $answerRecord = $this->getAnswerRecordService()->get($assessmentResponse['answer_record_id']);
        if (empty($answerRecord['is_options_seq_random'])) {
            return $assessmentResponse;
        }
        $answerRandomSeqRecord = $this->getAnswerRandomSeqRecordDao()->getByAnswerRecordId($answerRecord['id']);
        if (empty($answerRandomSeqRecord['options_random_seq'])) {
            return $assessmentResponse;
        }

        return $this->restoreAssessmentResponseOptionSeq($assessmentResponse, $answerRandomSeqRecord['options_random_seq']);
    }

    public function shuffleQuestionReportsAndConvertOptionsIfNecessary($questionReports, $answerRecordId)
    {
        if (empty($questionReports)) {
            return $questionReports;
        }

        $answerRecord = $this->getAnswerRecordService()->get($answerRecordId);
        if (!$this->isRandomSeq($answerRecord)) {
            return $questionReports;
        }
        $answerRandomSeqRecord = $this->getAnswerRandomSeqRecordDao()->getByAnswerRecordId($answerRecord['id']);
        if ($answerRecord['is_items_seq_random']) {
            $assessmentSnapshot = $this->getAssessmentService()->getAssessmentSnapshotBySnapshotAssessmentId($answerRecord['assessment_id']);
            $questionReports = $this->shuffleQuestionReports($questionReports, $answerRandomSeqRecord['items_random_seq'], $assessmentSnapshot['sections_snapshot'] ?? []);
        }
        if ($answerRecord['is_options_seq_random']) {
            $questionReports = $this->convertQuestionReportOptions($questionReports, $answerRandomSeqRecord['options_random_seq']);
        }

        return $questionReports;
    }

    private function isRandomSeq($answerRecord)
    {
        return $answerRecord['is_items_seq_random'] || $answerRecord['is_options_seq_random'];
    }

    /**
     * @param $sectionItems
     * @return array
     * 构建以section_id为key，打乱的itemId列表为val的数组
     */
    private function buildAssessmentItemsRandomSeq($sectionItems)
    {
        $itemsSectionGroup = ArrayToolkit::group($sectionItems, 'section_id');
        $randomItems = [];
        foreach ($itemsSectionGroup as $sectionId => $items) {
            $itemIds = array_column($items, 'item_id');
            shuffle($itemIds);
            $randomItems[$sectionId] = $itemIds;
        }

        return $randomItems;
    }

    /**
     * @param $sectionItems
     * @return array
     * 构建以questionId为key，打乱的选项列表为val的数组
     */
    private function buildAssessmentChoiceItemOptionsRandomSeq($sectionItems)
    {
        $choiceItemIds = $this->findChoiceItemIds($sectionItems);
        if (empty($choiceItemIds)) {
            return [];
        }
        $questions = $this->getItemService()->searchQuestions(['item_ids' => $choiceItemIds], [], 0, PHP_INT_MAX, ['id', 'answer_mode', 'response_points']);
        $randomOptions = [];
        foreach ($questions as $question) {
            $options = array_column(array_column($question['response_points'], $this->getAnswerModeInputType($question['answer_mode'])), 'val');
            shuffle($options);
            $randomOptions[$question['id']] = $options;
        }

        return $randomOptions;
    }

    private function shuffleAssessmentItems($assessment, $randomSeq)
    {
        $assessmentSnapshot = $this->getAssessmentService()->getAssessmentSnapshotBySnapshotAssessmentId($assessment['id']);
        $sectionSnapshot = empty($assessmentSnapshot) ? [] : array_flip($assessmentSnapshot['sections_snapshot']);
        foreach ($assessment['sections'] as &$section) {
            $sectionId = $sectionSnapshot[$section['id']] ?? $section['id'];
            $section['items'] = $this->shuffleItems($section['items'], $randomSeq[$sectionId]);
        }

        return $assessment;
    }

    private function shuffleAssessmentChoiceItemOptions($assessment, $randomSeq)
    {
        foreach ($assessment['sections'] as &$section) {
            foreach ($section['items'] as &$item) {
                if (!$this->isItemNeedShuffleOptions($item)) {
                    continue;
                }
                foreach ($item['questions'] as &$question) {
                    $question['response_points'] = $this->shuffleOptions($question, $randomSeq[$question['id']]);
                    $question['answer'] = $this->convertOptions($question['answer'], $randomSeq[$question['id']]);
                }
            }
        }

        return $assessment;
    }

    private function restoreAssessmentResponseOptionSeq($assessmentResponse, $randomSeq)
    {
        $sectionItems = $this->getSectionItemService()->searchAssessmentSectionItems(['assessment_id' => $assessmentResponse['assessment_id']], [], 0, PHP_INT_MAX, ['item_id']);
        $choiceItemIds = $this->findChoiceItemIds($sectionItems);
        $choiceItemIdMap = array_flip($choiceItemIds);
        foreach ($assessmentResponse['section_responses'] as &$sectionResponse) {
            foreach ($sectionResponse['item_responses'] as &$itemResponse) {
                if (!isset($choiceItemIdMap[$itemResponse['item_id']])) {
                    continue;
                }
                foreach ($itemResponse['question_responses'] as &$questionResponse) {
                    $questionResponse['response'] = $this->restoreOptionsToOriginalSeq($questionResponse['response'], $randomSeq[$questionResponse['question_id']]);
                }
            }
        }

        return $assessmentResponse;
    }

    private function shuffleQuestionReports($questionReports, $randomSeq, $sectionSnapshot)
    {
        if (empty($randomSeq)) {
            return $questionReports;
        }
        $sectionReports = ArrayToolkit::group($questionReports, 'section_id');
        foreach ($sectionReports as $sectionId => $sectionReport) {
            $sectionReports[$sectionId] = ArrayToolkit::group($sectionReport, 'item_id');
        }
        $shuffledQuestionReports = [];
        foreach ($randomSeq as $sectionId => $itemIds) {
            $sectionId = $sectionSnapshot[$sectionId] ?? $sectionId;
            foreach ($itemIds as $itemId) {
                $shuffledQuestionReports = array_merge($shuffledQuestionReports, $sectionReports[$sectionId][$itemId]);
            }
        }

        return $shuffledQuestionReports;
    }

    private function convertQuestionReportOptions($questionReports, $randomSeq)
    {
        if (empty($randomSeq)) {
            return $questionReports;
        }
        $choiceItemIds = $this->findChoiceItemIds($questionReports);
        $choiceItemIdMap = array_flip($choiceItemIds);
        foreach ($questionReports as &$questionReport) {
            if (!isset($choiceItemIdMap[$questionReport['item_id']])) {
                continue;
            }
            $questionReport['response'] = $this->convertOptions($questionReport['response'], $randomSeq[$questionReport['question_id']]);
        }

        return $questionReports;
    }

    private function findChoiceItemIds($items)
    {
        if (empty($items)) {
            return [];
        }
        $choiceItems = $this->getItemService()->searchItemsIncludeDeleted([
            'ids' => array_column($items, 'item_id'),
            'types' => $this->getChoiceItemTypes(),
        ], [], 0, PHP_INT_MAX, ['id']);

        return array_column($choiceItems, 'id');
    }

    /**
     * @param $items
     * @param $randomSeq
     * @return array
     * 根据`buildAssessmentItemsRandomSeq`生成的itemId顺序重排$items，并更新item和question的seq
     */
    private function shuffleItems($items, $randomSeq)
    {
        $itemStartSeq = $items[0]['seq'];
        $questionStartSeq = $items[0]['questions'][0]['seq'];
        $items = array_column($items, null, 'id');
        $randomItems = [];
        foreach ($randomSeq as $itemId) {
            $items[$itemId]['seq'] = $itemStartSeq++;
            foreach ($items[$itemId]['questions'] as &$question) {
                $question['seq'] = $questionStartSeq++;
            }
            $randomItems[] = $items[$itemId];
        }

        return $randomItems;
    }

    private function isItemNeedShuffleOptions($item)
    {
        return 1 != $item['isDelete'] && in_array($item['type'], $this->getChoiceItemTypes());
    }

    /**
     * @param $question
     * @param $randomSeq
     * @return mixed
     * 根据`buildAssessmentChoiceItemOptionsRandomSeq`生成的顺序重排选项
     */
    private function shuffleOptions($question, $randomSeq)
    {
        $input = $this->getAnswerModeInputType($question['answer_mode']);
        $options = array_column($question['response_points'], $input);
        $options = array_column($options, null, 'val');
        foreach ($question['response_points'] as $index => &$responsePoint) {
            $responsePoint[$input]['text'] = $options[$randomSeq[$index]]['text'];
        }

        return $question['response_points'];
    }

    private function restoreOptionsToOriginalSeq($options, $randomOptions)
    {
        $originalOptions = $randomOptions;
        sort($originalOptions);
        $optionMap = array_combine($originalOptions, $randomOptions);
        foreach ($options as &$option) {
            $option = $optionMap[$option];
        }
        sort($options);

        return $options;
    }

    private function convertOptions($options, $randomOptions)
    {
        $originalOptions = $randomOptions;
        sort($originalOptions);
        $optionMap = array_combine($randomOptions, $originalOptions);
        foreach ($options as &$option) {
            $option = $optionMap[$option];
        }
        sort($options);

        return $options;
    }

    private function getChoiceItemTypes()
    {
        return [SingleChoiceItem::TYPE, UncertainChoiceItem::TYPE, ChoiceItem::TYPE];
    }

    private function getAnswerModeInputType($answerMode)
    {
        $answerModeInputTypes = [
            SingleChoiceAnswerMode::NAME => SingleChoiceAnswerMode::INPUT_TYPE,
            UncertainChoiceAnswerMode::NAME => UncertainChoiceAnswerMode::INPUT_TYPE,
            ChoiceAnswerMode::NAME => ChoiceAnswerMode::INPUT_TYPE,
        ];

        return $answerModeInputTypes[$answerMode];
    }

    /**
     * @return AnswerRecordService
     */
    protected function getAnswerRecordService()
    {
        return $this->biz->service('ItemBank:Answer:AnswerRecordService');
    }

    /**
     * @return AssessmentService
     */
    protected function getAssessmentService()
    {
        return $this->biz->service('ItemBank:Assessment:AssessmentService');
    }

    /**
     * @return AssessmentSectionItemService
     */
    protected function getSectionItemService()
    {
        return $this->biz->service('ItemBank:Assessment:AssessmentSectionItemService');
    }

    /**
     * @return ItemService
     */
    protected function getItemService()
    {
        return $this->biz->service('ItemBank:Item:ItemService');
    }

    /**
     * @return AnswerRandomSeqRecordDao
     */
    protected function getAnswerRandomSeqRecordDao()
    {
        return $this->biz->dao('ItemBank:Answer:AnswerRandomSeqRecordDao');
    }
}
