<?php

namespace Codeages\Biz\ItemBank\Answer\Service\Impl;

use Codeages\Biz\Framework\Util\ArrayToolkit;
use Codeages\Biz\ItemBank\Answer\Dao\AnswerRandomSeqRecordDao;
use Codeages\Biz\ItemBank\Answer\Service\AnswerRandomSeqService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerRecordService;
use Codeages\Biz\ItemBank\Assessment\Service\AssessmentSectionItemService;
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
        if (empty($answerRecord['is_items_seq_random']) && empty($answerRecord['is_options_seq_random'])) {
            return;
        }
        $sectionItems = $this->getSectionItemService()->searchAssessmentSectionItems(['assessment_id' => $answerRecord['assessment_id']], [], 0, PHP_INT_MAX, ['item_id', 'section_id']);
        if (empty($sectionItems)) {
            return;
        }

        return $this->getAnswerRandomSeqRecordDao()->create([
            'answer_record_id' => $answerRecord['id'],
            'items_random_seq' => $this->generateAssessmentItemsRandomSeq($sectionItems),
            'options_random_seq' => $this->generateAssessmentChoiceItemOptionsRandomSeq($sectionItems),
        ]);
    }

    public function shuffleItemsAndOptions($assessment, $answerRecordId)
    {
        $answerRandomSeqRecord = $this->getAnswerRandomSeqRecordDao()->getbyAnswerRecordId($answerRecordId);
        foreach ($assessment['sections'] as &$section) {
            $section['items'] = $this->shuffleItems($section['items'], $answerRandomSeqRecord['items_random_seq'][$section['id']]);
            foreach ($section['items'] as &$item) {
                if (1 != $item['isDelete'] && in_array($item['type'], $this->getChoiceItemTypes())) {
                    foreach ($item['questions'] as &$question) {
                        $question['response_points'] = $this->shuffleOptions($question, $answerRandomSeqRecord['options_random_seq'][$question['id']]);
                    }
                }
            }
        }

        return $assessment;
    }

    protected function generateAssessmentItemsRandomSeq($sectionItems)
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

    protected function generateAssessmentChoiceItemOptionsRandomSeq($sectionItems)
    {
        $choiceItems = $this->getItemService()->searchItems([
            'ids' => array_column($sectionItems, 'item_id'),
            'types' => $this->getChoiceItemTypes(),
        ], [], 0, PHP_INT_MAX, ['id']);
        if (empty($choiceItems)) {
            return [];
        }
        $questions = $this->getItemService()->searchQuestions(['itemIds' => array_column($choiceItems, 'id')], [], 0, PHP_INT_MAX, ['id', 'answer_mode', 'response_points']);
        $randomOptions = [];
        $answerModes = [];
        foreach ($questions as $question) {
            $options = array_column(array_column($question['response_points'], $this->getAnswerModeInputType($question['answer_mode'])), 'val');
            shuffle($options);
            $randomOptions[$question['id']] = $options;
        }

        return $randomOptions;
    }

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
