<?php

namespace Codeages\Biz\ItemBank\Assessment\Service\Impl;

use Codeages\Biz\Framework\Util\ArrayToolkit;
use Codeages\Biz\ItemBank\Assessment\Dao\AssessmentSectionItemDao;
use Codeages\Biz\ItemBank\Assessment\Service\AssessmentSectionItemService;
use Codeages\Biz\ItemBank\BaseService;
use Codeages\Biz\ItemBank\Item\Service\ItemService;

class AssessmentSectionItemServiceImpl extends BaseService implements AssessmentSectionItemService
{
    public function getAssessmentSectionItem($id)
    {
        return $this->getAssessmentSectionItemDao()->get($id);
    }

    public function getItemByAssessmentIdAndItemId($assessmentId, $itemId)
    {
        return $this->getAssessmentSectionItemDao()->getByAssessmentIdAndItemId($assessmentId, $itemId);
    }

    public function findSectionItemsByAssessmentId($assessmentId)
    {
        return $this->getAssessmentSectionItemDao()->findByAssessmentId($assessmentId);
    }

    public function findSectionItemDetailByAssessmentId($assessmentId)
    {
        $assessmentItems = $this->getAssessmentSectionItemDao()->findByAssessmentId($assessmentId);
        $items = $this->getItemService()->findItemsByIdsIncludeDeleted(ArrayToolkit::column($assessmentItems, 'item_id'), true);
        foreach ($assessmentItems as &$assessmentItem) {
            $assessmentItem = $this->convertItem(empty($items[$assessmentItem['item_id']]) ? [] : $items[$assessmentItem['item_id']], $assessmentItem);
        }

        return $assessmentItems;
    }

    protected function convertItem($item = array(), $assessmentItem)
    {
        $item['isDelete'] = empty($item) ? 1 : 0;
        $item['seq'] = $assessmentItem['seq'];
        $item['score'] = $assessmentItem['score'];
        $item['id'] = $assessmentItem['item_id'];
        $item['section_id'] = $assessmentItem['section_id'];
        $itemQuestions = ArrayToolkit::index(empty($item['questions']) ? [] : $item['questions'], 'id');
        $scoreRules = ArrayToolkit::index($assessmentItem['score_rule'], 'question_id');
        $item['questions'] = [];
        foreach ($scoreRules as $questionId => $scoreRule) {
            if (!empty($itemQuestions[$questionId])) {
                $question = $this->getScoreRuleProcessor()->setQuestionScore($itemQuestions[$questionId], $scoreRule['rule']);
                $question['isDelete'] = 0;
                $question['seq'] = empty($scoreRule['seq']) ? '1' : $scoreRule['seq'];
            } else {
                $question = [
                    'id' => $questionId,
                    'seq' => empty($scoreRule['seq']) ? '1' : $scoreRule['seq'],
                    'isDelete' => 1,
                ];
            }

            if (!empty($scoreRule['rule'])) {
                $scoreRule['rule'] = ArrayToolkit::index($scoreRule['rule'], 'name');
                if (!empty($scoreRule['rule']['part_right']) && !empty($scoreRule['rule']['part_right']['score_rule'])) {
                    $question['score_rule'] = $scoreRule['rule']['part_right']['score_rule'];
                }
            }
            $item['questions'][] = $question;
        }

        return $item;
    }

    public function createAssessmentSectionItem($item, $section)
    {
        $questionScoreRule = [];
        $questionScore = [];
        foreach ($item['questions'] as $question) {
            $questionScoreRule[] = [
                'question_id' => $question['id'],
                'seq' => $question['seq'],
                'rule' => $this->getScoreRuleProcessor()->processRule($question),
            ];
            $questionScore[] = [
                'question_id' => $question['id'],
                'score' => $question['score'],
            ];
        }

        $sectionItem = [
            'assessment_id' => $section['assessment_id'],
            'section_id' => $section['id'],
            'item_id' => $item['id'],
            'seq' => $item['seq'],
            'score' => array_sum(ArrayToolkit::column($item['questions'], 'score')),
            'question_scores' => $questionScore,
            'score_rule' => $questionScoreRule,
            'question_count' => count($item['questions']),
        ];

        return $this->getAssessmentSectionItemDao()->create($sectionItem);
    }

    public function updateAssessmentSectionItem($id, $fields)
    {
        return $this->getAssessmentSectionItemDao()->update($id, $fields);
    }

    public function deleteAssessmentSectionItem($id)
    {
        return $this->getAssessmentSectionItemDao()->delete($id);
    }

    public function deleteAssessmentSectionItemsByAssessmentId($assessmentId)
    {
        return $this->getAssessmentSectionItemDao()->deleteByAssessmentId($assessmentId);
    }

    public function countAssessmentSectionItems($conditions)
    {
        return $this->getAssessmentSectionItemDao()->count($conditions);
    }

    public function searchAssessmentSectionItems($conditions, $orderBys, $start, $limit, $columns = array())
    {
        return $this->getAssessmentSectionItemDao()->search($conditions, $orderBys, $start, $limit, $columns);
    }

    public function findSectionItemsByAssessmentIds($assessmentIds)
    {
        return $this->getAssessmentSectionItemDao()->findByAssessmentIds($assessmentIds);
    }

    public function createAssessmentSectionItems($items)
    {
        $this->getAssessmentSectionItemDao()->batchCreate($items);
    }

    public function deleteAssessmentSectionItems($toDeleteSectionItems)
    {
        if (empty($toDeleteSectionItems)) {
            return;
        }
        $this->getAssessmentSectionItemDao()->batchDelete(['ids' => array_column($toDeleteSectionItems, 'id')]);
        $this->sortAssessmentSectionItemsSeq(ArrayToolkit::uniqueColumn($toDeleteSectionItems, 'assessment_id'));
    }

    public function findDeletedAssessmentSectionItems($assessmentId)
    {
        $sectionItems = $this->findSectionItemsByAssessmentId($assessmentId);
        $items = $this->getItemService()->findItemsByIdsIncludeDeleted(array_column($sectionItems, 'item_id'));

        return array_filter($sectionItems, function ($sectionItem) use ($items) {
            return empty($items[$sectionItem['item_id']]);
        });
    }

    private function sortAssessmentSectionItemsSeq($assessmentIds)
    {
        $sectionItems = $this->searchAssessmentSectionItems(
            ['assessmentIds' => $assessmentIds],
            ['assessment_id' => 'ASC', 'seq' => 'ASC'],
            0,
            PHP_INT_MAX,
            ['id', 'assessment_id', 'seq', 'score_rule']
        );
        $sectionItems = ArrayToolkit::group($sectionItems, 'assessment_id');
        $updateSectionItems = [];
        foreach ($sectionItems as $singleAssessmentSectionItems) {
            $questionSeq = 1;
            foreach ($singleAssessmentSectionItems as $index => $sectionItem) {
                $scoreRules = $sectionItem['score_rule'];
                foreach ($scoreRules as &$scoreRule) {
                    $scoreRule['seq'] = $questionSeq++;
                }
                $updateSectionItems[$sectionItem['id']] = [
                    'seq' => $index + 1,
                    'score_rule' => $scoreRules,
                ];
            }
        }
        if ($updateSectionItems) {
            $this->getAssessmentSectionItemDao()->batchUpdate(array_keys($updateSectionItems), $updateSectionItems);
        }
    }

    protected function getScoreRuleProcessor()
    {
        return $this->biz['score_rule_processor'];
    }

    /**
     * @return AssessmentSectionItemDao
     */
    protected function getAssessmentSectionItemDao()
    {
        return $this->biz->dao('ItemBank:Assessment:AssessmentSectionItemDao');
    }

    /**
     * @return ItemService
     */
    protected function getItemService()
    {
        return $this->biz->service('ItemBank:Item:ItemService');
    }
}
